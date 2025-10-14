<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'order_number',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'payment_method',
        'payment_status',
        'payment_intent_id',
        'billing_address',
        'shipping_address',
        'notes',
        'special_instructions',
        'estimated_delivery_date',
        'tracking_number',
        'is_gang_sheet',
        'gang_sheet_data',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => OrderStatus::class,
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'estimated_delivery_date' => 'date',
        'is_gang_sheet' => 'boolean',
        'gang_sheet_data' => 'array',
    ];

    protected $appends = [
        'formatted_total',
        'status_label',
        'status_color'
    ];

    /**
     * Get the customer that owns the order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get order items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'DTF-' . date('Y') . '-' . strtoupper(Str::random(8));
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status->color();
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return $this->status->canBeCancelled();
    }

    /**
     * Check if order is active
     */
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    /**
     * Update order status
     */
    public function updateStatus(OrderStatus $status): void
    {
        // Log status change
        OrderStatusHistory::create([
            'order_id' => $this->id,
            'status' => $status,
            'from_status' => $this->status,
            'to_status' => $status,
            'changed_at' => now(),
            'notes' => "Status changed to {$status->label()}",
        ]);

        $this->status = $status;
        $this->save();

    }

    public function statusHistory()
    {
        return OrderStatusHistory::where('order_id', $this->id);
    }

    /**
     * Calculate totals
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('total_price');

        // Calculate tax (assuming 8.5% tax rate)
        $this->tax_amount = $this->subtotal * 0.085;

        // Calculate shipping (free for orders over $50)
        $this->shipping_amount = $this->subtotal >= 50 ? 0 : 9.99;

        // Calculate total
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->shipping_amount - $this->discount_amount;

        $this->save();
    }

    /**
     * Scope for active orders
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            OrderStatus::PENDING,
            OrderStatus::PROCESSING,
            OrderStatus::PRINTING,
            OrderStatus::SHIPPED,
        ]);
    }

    /**
     * Scope for gang sheet orders
     */
    public function scopeGangSheet($query)
    {
        return $query->where('is_gang_sheet', true);
    }

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items()->sum('quantity');
    }
}
