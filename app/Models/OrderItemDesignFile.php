<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemDesignFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'file_path',
        'original_filename',
        'file_type',
        'file_size',
        'dimensions',
        'resolution',
        'color_mode',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'dimensions' => 'array',
        'resolution' => 'array',
    ];

    /**
     * Get the order item that owns the design file
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the full URL for the file
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Get human readable file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->file_type, 'image/');
    }

    /**
     * Check if file is a PDF
     */
    public function isPdf(): bool
    {
        return $this->file_type === 'application/pdf';
    }

    /**
     * Check if file is a vector format
     */
    public function isVector(): bool
    {
        return in_array($this->file_type, [
            'image/svg+xml',
            'application/illustrator',
            'image/x-eps'
        ]);
    }
}
