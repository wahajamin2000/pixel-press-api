<?php

namespace App\Http\Controllers\Api\V1\Modules\Product;

use App\Enums\ProductStatus;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\Lookups\ProductLookupResource;
use App\Http\Resources\Modules\Order\OrderResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Traits\HasMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;

class ProductApiController extends Controller
{
    use HasMedia;

    /**
     * Display a listing of products
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category');

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by featured
        if ($request->has('featured')) {
            $query->featured();
        }

        // Only active products for public API
        if (!auth()->check() || auth()->user()->level !== User::LEVEL_SUPER_ADMIN) {
            $query->active();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return $this->successResponse('Fetched Successfully!', [
            'products' => ProductLookupResource::collection($products),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ]);
    }

    public function getActiveProds(Request $request): JsonResponse
    {
        $query = Product::with('category')->active();

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by featured
        if ($request->has('featured')) {
            $query->featured();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return $this->successResponse('Fetched Successfully!', [
            'products' => ProductLookupResource::collection($products),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Get validated data and remove images from it (since images is not a database field)
            $validatedData = $request->validated();
            unset($validatedData['images']); // Remove images from the data to be saved to database

            $product = Product::create($validatedData);

            $product->created_by = Auth::id();
            $product->save();

            // Handle image uploads separately
            if ($request->hasFile('images')) {
                $this->handleImageUploads($product, $request->file('images'));
            }

            DB::commit();

            $product->load('images','category');

            return $this->successResponse('Product created successfully', [
                    'product' => new ProductResource($product)
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e
            ]);
            return $this->errorResponse('Failed to create product', 500, [
                    'error' => 'Failed to create product: ' . $e->getMessage()
                ]
            );
        }
    }

    /**
     * Display the specified product
     */
    public function show(Product $product): JsonResponse
    {
        $product->load('images','category');

        return $this->successResponse('Product retrieved successfully',[
            'product' => new ProductResource($product)
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Get validated data and remove images and delete_images from it
            $validatedData = $request->validated();
            unset($validatedData['images']); // Remove images from the data to be saved to database
            unset($validatedData['delete_images']); // Remove delete_images from the data to be saved to database

            $product->update($validatedData);

            $product->updated_by = Auth::id();
            $product->save();

            // Handle image deletions first
            if ($request->has('delete_images') && is_array($request->delete_images)) {
                $this->handleImageDeletions($product, $request->delete_images);
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                $this->handleImageUploads($product, $request->file('images'));
            }

            DB::commit();

            $product->load('images','category');

            return $this->successResponse('Product updated successfully',[
                'product' => new ProductResource($product)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'request_data' => $request->all(),
                'exception' => $e
            ]);
            return $this->errorResponse('Failed to update product', 500, [
                    'error' => 'Failed to update product: ' . $e->getMessage()
                ]
            );
        }
    }

    /**
     * Update the specified product status
     */
    public function updateStatus(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'status' => ['required', new Enum(ProductStatus::class)],
        ]);

        $product->status = $request->status ?? $product->status;
        $product->updated_by = Auth::id();
        $product->save();

        return $this->successResponse('Product status updated successfully',[]);
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Delete associated images
            foreach ($product->images as $image) {
                $this->deleteFile($image->image_path);
                $image->delete();
            }

            $product->delete();

            DB::commit();

            return $this->successResponse('Product deleted successfully', []);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product deletion failed: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'exception' => $e
            ]);
            return $this->errorResponse('Failed to delete product', 500, [
                    'error' => 'Failed to delete product: ' . $e->getMessage()
                ]
            );
        }
    }

    /**
     * Get featured products
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 8);

        $products = Product::with('category')
            ->active()
            ->featured()
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();

        return $this->successResponse('Featured products retrieved successfully',[
            'products' => ProductLookupResource::collection($products)
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'per_page' => 'integer|min:1|max:50',
        ]);

        $products = Product::with('images','category')
            ->active()
            ->search($request->q)
            ->orderBy('name')
            ->paginate($request->get('per_page', 15));

        return $this->successResponse('Search results retrieved successfully', [
            'products' => ProductLookupResource::collection($products),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ]);
    }

    /**
     * Handle image uploads for a product
     */
    private function handleImageUploads(Product $product, array $images): void
    {
        $currentImageCount = $product->images()->count();
        $isFirstImage = $currentImageCount === 0;

        foreach ($images as $index => $image) {
            if ($this->validateFileType($image, $this->getAllowedImageTypes())) {
                $path = $this->uploadFile($image, 'products');

                if ($path) { // Only create if upload was successful
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'alt_text' => $product->name . ' - Image ' . ($currentImageCount + $index + 1),
                        'is_primary' => $isFirstImage && $index === 0,
                        'sort_order' => $currentImageCount + $index + 1,
                    ]);
                } else {
                    Log::warning('Failed to upload image for product', [
                        'product_id' => $product->id,
                        'image_index' => $index
                    ]);
                }
            } else {
                Log::warning('Invalid file type for product image', [
                    'product_id' => $product->id,
                    'image_index' => $index,
                    'file_type' => $image->getMimeType()
                ]);
            }
        }
    }

    /**
     * Handle image deletions for a product
     */
    private function handleImageDeletions(Product $product, array $imageIds): void
    {
        $images = $product->images()->whereIn('id', $imageIds)->get();
        $wasPrimaryDeleted = false;

        foreach ($images as $image) {
            if ($image->is_primary) {
                $wasPrimaryDeleted = true;
            }

            if (!$this->deleteFile($image->image_path)) {
                Log::warning('Failed to delete image file', [
                    'image_id' => $image->id,
                    'image_path' => $image->image_path
                ]);
            }

            $image->delete();
        }

        // If primary image was deleted, make the first remaining image primary
        if ($wasPrimaryDeleted && !$product->images()->where('is_primary', true)->exists()) {
            $firstImage = $product->images()->orderBy('sort_order')->first();
            if ($firstImage) {
                $firstImage->update(['is_primary' => true]);
            }
        }
    }

    /**
     * Update product image
     */
    public function updateImage(Request $request, Product $product, ProductImage $image): JsonResponse
    {
        $request->validate([
            'alt_text' => 'string|max:255',
            'is_primary' => 'boolean',
            'sort_order' => 'integer|min:1',
        ]);

        // If setting as primary, remove primary from other images
        if ($request->boolean('is_primary')) {
            $product->images()->where('id', '!=', $image->id)->update(['is_primary' => false]);
        }

        $image->update($request->only(['alt_text', 'is_primary', 'sort_order']));

        return $this->successResponse('Image updated successfully',[
            'image' => $image
        ]);
    }

    /**
     * Delete product image
     */
    public function deleteImage(Product $product, ProductImage $image): JsonResponse
    {
        try {
            $isPrimary = $image->is_primary;

            if (!$this->deleteFile($image->image_path)) {
                Log::warning('Failed to delete image file during image deletion', [
                    'image_id' => $image->id,
                    'image_path' => $image->image_path
                ]);
            }

            $image->delete();

            // If primary image was deleted, make the first remaining image primary
            if ($isPrimary) {
                $firstImage = $product->images()->orderBy('sort_order')->first();
                if ($firstImage) {
                    $firstImage->update(['is_primary' => true]);
                }
            }

            return $this->successResponse('Image deleted successfully', []);
        } catch (\Exception $e) {
            Log::error('Failed to delete product image', [
                'image_id' => $image->id,
                'product_id' => $product->id,
                'exception' => $e
            ]);
            return $this->errorResponse('Failed to delete image', 500);
        }
    }
}
