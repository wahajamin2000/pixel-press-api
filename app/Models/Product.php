<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasMedia;

    protected $guarded = [];

    protected $casts = [
        'status' => ProductStatus::class,
        'price' => 'decimal:2',
        'base_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'dimensions' => 'array',
        'color_options' => 'array',
        'size_options' => 'array',
        'print_areas' => 'array',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'formatted_price',
        'formatted_base_price',
        'primary_image_url',
        'all_images_urls'
    ];

    /**
     * Get product images
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get order items for this product
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get formatted base price
     */
    public function getFormattedBasePriceAttribute(): string
    {
        return '$' . number_format($this->base_price, 2);
    }

    /**
     * Get primary image URL
     */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        $primaryImage = $this->images()->where('is_primary', true)->first();

        if ($primaryImage) {
            return $this->generateImageUrl($primaryImage->image_path);
        }

        $firstImage = $this->images()->first();
        return $firstImage ? $this->generateImageUrl($firstImage->image_path) : null;
    }

    /**
     * Get all image URLs
     */
    public function getAllImagesUrlsAttribute(): array
    {
        return $this->images->map(function ($image) {
            return [
                'id' => $image->id,
                'url' => $this->generateImageUrl($image->image_path),
                'alt_text' => $image->alt_text,
                'is_primary' => $image->is_primary,
                'sort_order' => $image->sort_order,
            ];
        })->toArray();
    }

    /**
     * Generate image URL from path
     */
    private function generateImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // If the path already contains a full URL, return it
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Generate the full URL using Laravel's asset helper for storage
        return asset('storage/' . $path);
    }
//    /**
//     * Get primary image URL
//     */
//    public function getPrimaryImageUrlAttribute(): ?string
//    {
//        $primaryImage = $this->images()->where('is_primary', true)->first();
//
//        if ($primaryImage) {
//            return $this->getFileUrl($primaryImage->image_path);
//        }
//
//        $firstImage = $this->images()->first();
//        return $firstImage ? $this->getFileUrl($firstImage->image_path) : null;
//    }
//
//    /**
//     * Get all image URLs
//     */
//    public function getAllImagesUrlsAttribute(): array
//    {
//        return $this->images->map(function ($image) {
//            return [
//                'id' => $image->id,
//                'url' => $this->getFileUrl($image->image_path),
//                'alt_text' => $image->alt_text,
//                'is_primary' => $image->is_primary,
//                'sort_order' => $image->sort_order,
//            ];
//        })->toArray();
//    }

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('status', ProductStatus::ACTIVE);
    }

    /**
     * Scope for featured products
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        });
    }

    /**
     * Check if product is out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->status === ProductStatus::OUT_OF_STOCK;
    }

    /**
     * Check if product is available
     */
    public function isAvailable(): bool
    {
        return $this->status === ProductStatus::ACTIVE;
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class,'category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class,'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class,'deleted_by');
    }

}
