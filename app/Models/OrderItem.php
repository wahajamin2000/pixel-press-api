<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class OrderItem extends Model
{
    use HasFactory, HasMedia;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'product_name',
        'product_sku',
        'design_specifications',
        'print_dimensions',
        'dimensions',
        'color_options',
        'special_instructions',
        'gang_sheet_position',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'design_specifications' => 'array',
        'print_dimensions' => 'array',
        'dimensions' => 'array',
        'color_options' => 'array',
        'gang_sheet_position' => 'array',
    ];

    protected $appends = [
        'formatted_total_price',
        'design_files_urls'
    ];

    /**
     * Get the order that owns the item
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get design files for this order item
     */
    public function designFiles(): HasMany
    {
        return $this->hasMany(OrderItemDesignFile::class);
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return '$' . number_format($this->total_price, 2);
    }

    /**
     * Get design files URLs
     */
    public function getDesignFilesUrlsAttribute(): array
    {
        return $this->designFiles->map(function ($file) {
            return [
                'id' => $file->id,
                'url' => Storage::disk('public')->url($file->file_path),
                'filename' => $file->original_filename,
                'file_type' => $file->file_type,
                'file_size' => $file->file_size,
                'uploaded_at' => $file->created_at,
            ];
        })->toArray();
    }


    /**
     * Calculate total price based on quantity and unit price
     */
    public function calculateTotalPrice(): void
    {
        $this->total_price = $this->quantity * $this->unit_price;
        $this->save();
    }

    /**
     * Check if item has design files
     */
    public function hasDesignFiles(): bool
    {
        return $this->designFiles()->count() > 0;
    }

    /**
     * Get total design files count
     */
    public function getDesignFilesCountAttribute(): int
    {
        return $this->designFiles()->count();
    }

    /**
     * Check if item is part of gang sheet
     */
    public function isPartOfGangSheet(): bool
    {
        return !is_null($this->gang_sheet_position);
    }
}
