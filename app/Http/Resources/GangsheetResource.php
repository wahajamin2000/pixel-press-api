<?php

namespace App\Http\Resources;

use App\Http\Resources\Lookups\CategoryLookupResource;
use App\Http\Resources\Lookups\ProductLookupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GangsheetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'design_id' => $this->design_id,

            // Design Information
            'name' => $this->name,
            'file_name' => $this->file_name,
            'size' => $this->size,
            'order_type' => $this->order_type,
            'quality' => $this->quality,

            // Status
            'status' => $this->status,
            'is_generated' => $this->is_generated,
            'is_completed' => $this->is_completed,
            'is_ready_for_download' => $this->isReadyForDownload(),

            // URLs
            'download_url' => $this->download_url,
            'thumbnail_url' => $this->thumbnail_url,
            'edit_url' => $this->edit_url,

            // Images and Dimensions
            'images' => $this->images,
            'image_count' => $this->image_count,
            'width' => $this->width,
            'height' => $this->height,
            'dimensions' => $this->when($this->width && $this->height, "{$this->width}x{$this->height}"),

            // Timestamps
            'generated_at' => $this->generated_at,
            'last_synced_at' => $this->last_synced_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Related Order Items
            'order_items_count' => $this->whenCounted('orderItems'),
        ];
    }
}
