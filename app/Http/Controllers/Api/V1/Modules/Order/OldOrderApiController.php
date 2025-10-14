<?php

namespace App\Http\Controllers\Api\V1\Modules\Order;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\Lookups\OrderLookupResource;
use App\Http\Resources\Modules\Order\OrderResource;
use App\Http\Resources\Modules\Order\OrderStatusHistoryResource;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemDesignFile;
use App\Models\Product;
use App\Traits\HasMedia;
use App\Enums\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OldOrderApiController extends Controller
{
    use HasMedia;

    /**
     * Display a listing of orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items.product', 'items.designFiles']);

        $user = auth()->user();

        // Restrict customers to only their own orders
        if ($user->level === User::LEVEL_CUSTOMER) {
            $query->where('user_id', $user->id);
        }

        // Filter by user (only for admins)
        if ($request->has('user_id') && $user->level === User::LEVEL_SUPER_ADMIN) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by order number
        if ($request->has('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        return $this->successResponse('Fetched Successfully!', [
            'orders' => OrderLookupResource::collection($orders),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created order (Guest Checkout)
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Check if user exists or create new one
            $user = $this->findOrCreateUser($request->validated());

            // Pre-calculate order totals before creating order
            $orderCalculations = $this->calculateOrderTotals($request->validated());

            // Create order with calculated values
            $order = $this->createOrder($user, $request->validated(), $orderCalculations);

            // Create order items
            $this->createOrderItems($order, $request->validated()['items']);

            // Recalculate totals (in case of any rounding differences)
            $order->refresh();
            $this->recalculateOrderTotals($order);

            DB::commit();

            $order->load(['user', 'items.product', 'items.designFiles']);

            if (isset($user->temporary_password)) {
                $this->sendWelcomeEmail($user);
            }

            return $this->successResponse('Order created successfully', [
                    'order' => new OrderResource($order)
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create order', 500, [
                    'error' => 'Failed to create order: ' . $e->getMessage()
                ]
            );
        }
    }

    /**
     * Display the specified order
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['user', 'items.product', 'items.designFiles']);

        return $this->successResponse('Order retrieved successfully', [
                'order' => new OrderResource($order)
            ]
        );
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'value' => 'required|string|in:' . implode(',', OrderStatus::getValues()),
            'notes' => 'nullable|string|max:1000',
        ]);

        $newStatus = OrderStatus::from($request->value);
        $order->updateStatus($newStatus);

        $latestHistory = null;
        if ($request->notes) {
            // Add notes to the latest status history record
            $latestHistory = $order->statusHistory()->latest()->first();
            if ($latestHistory) {
                $latestHistory->update(['notes' => $request->notes]);
            }
        }

        return $this->successResponse('Order status updated successfully', [
                'status_history' => isset($latestHistory) ? new OrderStatusHistoryResource($latestHistory) : null,
            ]
        );
    }

    /**
     * Cancel an order
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        if (!$order->canBeCancelled()) {
            return $this->errorResponse('This order cannot be cancelled', 400);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $order->updateStatus(OrderStatus::CANCELLED);

        $latestHistory = null;
        // Add cancellation reason to status history
        if ($request->reason) {
            $latestHistory = $order->statusHistory()->latest()->first();
            if ($latestHistory) {
                $latestHistory->update(['notes' => 'Cancelled: ' . $request->reason]);
            }
        }

        return $this->successResponse('Order cancelled successfully', [
                'status_history' => isset($latestHistory) ? new OrderStatusHistoryResource($latestHistory) : null,
            ]
        );

    }

    /**
     * Get order status history
     */
    public function statusHistory(Order $order): JsonResponse
    {
        $history = $order->statusHistory()
            ->with('changedByUser')
            ->orderBy('changed_at', 'desc')
            ->get();

        return $this->successResponse('Order status history retrieved successfully', [
                'status_history' => isset($history) ? OrderStatusHistoryResource::collection($history) : [],
            ]
        );

    }

    /**
     * Upload design files for order item
     */
    public function uploadDesignFiles(Request $request, Order $order, OrderItem $orderItem): JsonResponse
    {
        $request->validate([
            'design_files' => 'required|array|max:10',
            'design_files.*' => 'file|mimes:jpeg,png,jpg,gif,webp,pdf,ai,eps|max:10240', // 10MB max
        ]);

        if ($orderItem->order_id !== $order->id) {

            return $this->errorResponse('Order item does not belong to this order', 400);
        }

        $uploadedFiles = [];

        foreach ($request->file('design_files') as $file) {
            if ($this->validateFileType($file, $this->getAllowedDesignTypes())) {
                $path = $this->uploadFile($file, 'designs');

                $designFile = OrderItemDesignFile::create([
                    'order_item_id' => $orderItem->id,
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);

                $uploadedFiles[] = $designFile;
            }
        }

        return $this->successResponse('Design files uploaded successfully', [
                'files' => $uploadedFiles
            ]
        );

    }

    /**
     * Find or create user
     */
    private function findOrCreateUser(array $data): User
    {
        $user = User::where('email', $data['customer']['email'])->customers()->first();

        if (!$user) {
            $user = User::createWithTemporaryPassword($data['customer']);
        }

        return $user;
    }
//
//    /**
//     * Create order
//     */
//    private function createOrder(User $user, array $data): Order
//    {
//        return Order::create([
//            'user_id' => $user->id,
//            'slug' => generate_slug($user->name, 32, 'ord-'),
//            'order_number' => Order::generateOrderNumber(),
//            'status' => OrderStatus::PENDING,
//            'currency' => 'USD',
//            'billing_address' => $data['billing_address'] ?? null,
//            'shipping_address' => $data['shipping_address'] ?? $data['billing_address'] ?? null,
//            'notes' => $data['notes'] ?? null,
//            'special_instructions' => $data['special_instructions'] ?? null,
//            'is_gang_sheet' => $data['is_gang_sheet'] ?? false,
//            'gang_sheet_data' => $data['gang_sheet_data'] ?? null,
//        ]);
//    }
//
//    /**
//     * Create order items
//     */
//    private function createOrderItems(Order $order, array $items): void
//    {
//        foreach ($items ?? [] as $itemData) {
//            $product = Product::findOrFail($itemData['product_id']);
//
//            $orderItem = OrderItem::create([
//                'order_id' => $order->id,
//                'product_id' => $product->id,
//                'quantity' => $itemData['quantity'],
//                'unit_price' => $product->price,
//                'total_price' => $product->price * $itemData['quantity'],
//                'product_name' => $product->name,
//                'product_sku' => $product->sku,
//                'design_specifications' => $itemData['design_specifications'] ?? null,
//                'dimensions' => $itemData['dimensions'] ?? null,
//                'color_options' => $itemData['color_options'] ?? null,
//                'special_instructions' => $itemData['special_instructions'] ?? null,
//                'gang_sheet_position' => $itemData['gang_sheet_position'] ?? null,
//            ]);
//
//            // Handle design file uploads if provided
//            if (isset($itemData['design_files']) && is_array($itemData['design_files'])) {
//                foreach ($itemData['design_files'] as $file) {
//                    if ($this->validateFileType($file, $this->getAllowedDesignTypes())) {
//                        $path = $this->uploadFile($file, 'designs');
//
//                        OrderItemDesignFile::create([
//                            'order_item_id' => $orderItem->id,
//                            'file_path' => $path,
//                            'original_filename' => $file->getClientOriginalName(),
//                            'file_type' => $file->getMimeType(),
//                            'file_size' => $file->getSize(),
//                        ]);
//                    }
//                }
//            }
//        }
//    }

    /**
     * Calculate order totals before creation
     */
    private function calculateOrderTotals(array $data): array
    {
        $subtotal = 0;
        $taxAmount = 0;
        $shippingAmount = 0;
        $discountAmount = 0;

        // Calculate subtotal from items
        foreach ($data['items'] as $itemData) {
            $product = Product::findOrFail($itemData['product_id']);
            $quantity = $itemData['quantity'];

            // Base price calculation
            $itemPrice = $product->price;

            // Apply any item-specific pricing (size, customization, etc.)
            $itemPrice = $this->calculateItemPrice($product, $itemData);

            $subtotal += $itemPrice * $quantity;
        }

        // Apply discount if provided
        if (!empty($data['discount_code'])) {
            $discountAmount = $this->calculateDiscountAmount($data['discount_code'], $subtotal);
        } elseif (!empty($data['custom_discount_amount'])) {
            $discountAmount = min($data['custom_discount_amount'], $subtotal);
        }

        // Calculate shipping
        $shippingMethod = $data['shipping_method'] ?? 'standard';
        $shippingAddress = $data['shipping_address'] ?? $data['billing_address'];
        $shippingAmount = $this->calculateShippingAmount($shippingMethod, $shippingAddress, $subtotal);

        // Calculate tax (after discount, before shipping)
        $taxableAmount = $subtotal - $discountAmount;
        if (empty($data['tax_exempt'])) {
            $billingAddress = $data['billing_address'];
            $taxAmount = $this->calculateTaxAmount($taxableAmount, $billingAddress);
        }

        // Gang sheet surcharge if applicable
        $gangSheetSurcharge = 0;
        if ($data['is_gang_sheet'] ?? false) {
            $gangSheetSurcharge = $this->calculateGangSheetSurcharge($data['items']);
        }

        // Calculate total
        $totalAmount = $subtotal - $discountAmount + $taxAmount + $shippingAmount + $gangSheetSurcharge;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'shipping_amount' => round($shippingAmount, 2),
            'discount_amount' => round($discountAmount, 2),
            'gang_sheet_surcharge' => round($gangSheetSurcharge, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * Calculate individual item price with customizations
     */
    private function calculateItemPrice(Product $product, array $itemData): float
    {
        $basePrice = $product->price;

        // Size-based pricing
        if (!empty($itemData['dimensions'])) {
            $width = $itemData['dimensions']['width'] ?? 0;
            $height = $itemData['dimensions']['height'] ?? 0;
            $area = $width * $height;

            // Add surcharge for larger sizes
            if ($area > 100) { // Example: surcharge for items over 100 sq inches
                $basePrice += ($area - 100) * 0.05; // $0.05 per sq inch over 100
            }
        }

        // Color options surcharge
        if (!empty($itemData['color_options']) && count($itemData['color_options']) > 1) {
            $additionalColors = count($itemData['color_options']) - 1;
            $basePrice += $additionalColors * 2.50; // $2.50 per additional color
        }

        // Design file processing fee
        if (!empty($itemData['design_files']) && count($itemData['design_files']) > 2) {
            $extraFiles = count($itemData['design_files']) - 2;
            $basePrice += $extraFiles * 5.00; // $5.00 per extra design file
        }

        // Special instructions surcharge
        if (!empty($itemData['special_instructions'])) {
            $basePrice += 10.00; // $10.00 for custom instructions
        }

        return $basePrice;
    }

    /**
     * Calculate discount amount
     */
    private function calculateDiscountAmount(string $discountCode, float $subtotal): float
    {
        // This would typically query a discounts/coupons table
        $discount = DiscountCode::where('code', $discountCode)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();

        if (!$discount) {
            return 0;
        }

        if ($discount->type === 'percentage') {
            return ($subtotal * $discount->value) / 100;
        } elseif ($discount->type === 'fixed') {
            return min($discount->value, $subtotal);
        }

        return 0;
    }

    /**
     * Calculate shipping amount
     */
    private function calculateShippingAmount(string $method, array $address, float $subtotal): float
    {
        // Free shipping threshold
        if ($subtotal >= 100) {
            return 0;
        }

        $baseRates = [
            'standard' => 8.99,
            'express' => 15.99,
            'overnight' => 25.99,
        ];

        $baseRate = $baseRates[$method] ?? $baseRates['standard'];

        // International shipping surcharge
        if (strtoupper($address['country']) !== 'USA') {
            $baseRate += 15.00;
        }

        return $baseRate;
    }

    /**
     * Calculate tax amount
     */
    private function calculateTaxAmount(float $taxableAmount, array $billingAddress): float
    {
        // Simple tax calculation - in reality, you'd use a tax service
        $taxRates = [
            'CA' => 0.0875, // 8.75%
            'NY' => 0.08,   // 8%
            'TX' => 0.0625, // 6.25%
            'FL' => 0.06,   // 6%
            // Add more states as needed
        ];

        $state = strtoupper($billingAddress['state']);
        $taxRate = $taxRates[$state] ?? 0;

        return $taxableAmount * $taxRate;
    }

    /**
     * Calculate gang sheet surcharge
     */
    private function calculateGangSheetSurcharge(array $items): float
    {
        if (count($items) < 2) {
            return 0;
        }

        // Base gang sheet setup fee
        $setupFee = 25.00;

        // Additional fee per item over 5
        if (count($items) > 5) {
            $extraItems = count($items) - 5;
            $setupFee += $extraItems * 5.00;
        }

        return $setupFee;
    }

    /**
     * Create order with calculated values
     */
    private function createOrder(User $user, array $data, array $calculations): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'slug' => generate_slug($user->name, 32, 'ord-'),
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::PENDING,
            'subtotal' => $calculations['subtotal'],
            'tax_amount' => $calculations['tax_amount'],
            'shipping_amount' => $calculations['shipping_amount'],
            'discount_amount' => $calculations['discount_amount'],
            'total_amount' => $calculations['total_amount'],
            'currency' => 'USD',
            'payment_status' => 'pending',
            'billing_address' => $data['billing_address'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? $data['billing_address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'special_instructions' => $data['special_instructions'] ?? null,
//            'is_gang_sheet' => $data['is_gang_sheet'] ?? false,
//            'gang_sheet_data' => $data['gang_sheet_data'] ?? null,
        ]);
    }

    /**
     * Recalculate order totals after items are created
     */
    private function recalculateOrderTotals(Order $order): void
    {
        $subtotal = $order->items->sum(function ($item) {
            return $item->unit_price * $item->quantity;
        });

        // Recalculate tax based on actual item totals
        $taxableAmount = $subtotal - $order->discount_amount;
        $taxAmount = $this->calculateTaxAmount($taxableAmount, $order->billing_address);

        // Update if there are differences (due to rounding, etc.)
        if (abs($order->subtotal - $subtotal) > 0.01 || abs($order->tax_amount - $taxAmount) > 0.01) {
            $order->update([
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'total_amount' => round($subtotal - $order->discount_amount + $taxAmount + $order->shipping_amount, 2),
            ]);
        }
    }

    /**
     * Create order items with calculated prices
     */
    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $itemData) {
            $product = Product::findOrFail($itemData['product_id']);

            // Calculate item price with customizations
            $unitPrice = $this->calculateItemPrice($product, $itemData);
            $totalPrice = $unitPrice * $itemData['quantity'];

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $itemData['quantity'],
                'unit_price' => round($unitPrice, 2),
                'total_price' => round($totalPrice, 2),
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'design_specifications' => $itemData['design_specifications'] ?? null,
                'dimensions' => $itemData['dimensions'] ?? null,
                'color_options' => $itemData['color_options'] ?? null,
                'special_instructions' => $itemData['special_instructions'] ?? null,
//                'gang_sheet_position' => $itemData['gang_sheet_position'] ?? null,
            ]);

            // Handle design file uploads
            if (isset($itemData['design_files']) && is_array($itemData['design_files'])) {
                foreach ($itemData['design_files'] as $file) {
                    if ($this->validateFileType($file, $this->getAllowedDesignTypes())) {
                        $path = $this->uploadFile($file, 'designs');

                        OrderItemDesignFile::create([
                            'order_item_id' => $orderItem->id,
                            'file_path' => $path,
                            'original_filename' => $file->getClientOriginalName(),
                            'file_type' => $file->getMimeType(),
                            'file_size' => $file->getSize(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Get allowed design file types
     */
    private function getAllowedDesignTypes(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'image/jpg',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/postscript', // .ai files
            'application/eps',        // .eps files
        ];
    }

    /**
     * Validate file type against allowed types
     */
    private function validateFileType($file, array $allowedTypes): bool
    {
        if (!$file || !$file->isValid()) {
            return false;
        }

        $mimeType = $file->getMimeType();
        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Upload file to storage
     */
    private function uploadFile($file, string $directory = 'uploads'): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');
        return $path;
    }
    /**
     * Send welcome email to new user
     */
    private function sendWelcomeEmail(User $user): void
    {
        // This would typically use Laravel's Mail facade
        // For now, we'll just log the action
        Log::info('Welcome email sent to user', [
            'user_id' => $user->id,
            'email' => $user->email,
            'temporary_password' => $user->temporary_password ?? 'N/A',
        ]);
    }
}



