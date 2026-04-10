<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gangsheet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gangsheet_products';
    protected $fillable = [
        'design_id',
        'name',
        'file_name',
        'size',
        'order_type',
        'quality',
        'status',
        'download_url',
        'thumbnail_url',
        'edit_url',
        'images',
        'metadata',
        'width',
        'height',
        'image_count',
        'generated_at',
        'last_synced_at',
    ];

    protected $casts = [
        'images' => 'array',
        'metadata' => 'array',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'image_count' => 'integer',
        'generated_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    protected $appends = [
        'is_generated',
        'is_completed',
    ];

    /**
     * Get order items that use this gangsheet
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if gangsheet has been generated
     */
    public function getIsGeneratedAttribute(): bool
    {
        return !is_null($this->generated_at);
    }

    /**
     * Check if gangsheet is completed
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if gangsheet is ready for download
     */
    public function isReadyForDownload(): bool
    {
        return $this->is_completed && !empty($this->download_url);
    }

    /**
     * Update gangsheet data from API response
     */
    public function updateFromApiResponse(array $data): void
    {
        $design = $data['design'] ?? [];

        $updateData = [
            'name' => $design['name'] ?? $this->name,
            'file_name' => $design['file_name'] ?? $this->file_name,
            'size' => $design['size'] ?? $this->size,
            'order_type' => $design['order_type'] ?? $this->order_type,
            'quality' => $design['quality'] ?? $this->quality,
            'status' => $design['status'] ?? $this->status,
            'download_url' => $design['download_url'] ?? $this->download_url,
            'thumbnail_url' => $design['thumbnail_url'] ?? $this->thumbnail_url,
            'edit_url' => $design['edit_url'] ?? $this->edit_url,
            'images' => $design['images'] ?? $this->images,
            'metadata' => $data,
            'last_synced_at' => now(),
        ];

        // Set generated_at if status is completed and it's not already set
        if (($design['status'] ?? '') === 'completed' && is_null($this->generated_at)) {
            $updateData['generated_at'] = now();
        }

        // Update image count
        if (isset($design['images']) && is_array($design['images'])) {
            $updateData['image_count'] = count($design['images']);
        }

        // Extract dimensions if available
        if (isset($design['width'])) {
            $updateData['width'] = $design['width'];
        }
        if (isset($design['height'])) {
            $updateData['height'] = $design['height'];
        }

//        $this->update($updateData);
        $this->fill($updateData);
    }

    /**
     * Create or update gangsheet from API response
     */

    public static function createOrUpdateFromApi(string $designId, array $apiResponse): self
    {
        // Use firstOrCreate to ensure the model EXISTS in database
        $gangsheet = static::firstOrCreate(
            ['design_id' => $designId],
            [
                'name' => $apiResponse['design']['name'] ?? 'Untitled Gang Sheet',
                'status' => $apiResponse['design']['status'] ?? 'pending',
            ]
        );

        // Now update with full API data
        $gangsheet->updateFromApiResponse($apiResponse);

        // Explicitly save the changes
        $gangsheet->save();

        return $gangsheet;
    }
    /**
     * Scope to get only completed gangsheets
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get only pending gangsheets
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get gangsheets that need syncing (older than X hours)
     */
    public function scopeNeedsSyncing($query, int $hours = 1)
    {
        return $query->where(function ($q) use ($hours) {
            $q->whereNull('last_synced_at')
                ->orWhere('last_synced_at', '<', now()->subHours($hours));
        })->whereNotIn('status', ['completed', 'failed']);
    }
}
