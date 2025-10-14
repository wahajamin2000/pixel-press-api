<?php

namespace App\Http\Resources\Modules\Order;

use App\Http\Resources\Lookups\ProductLookupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => new ProductLookupResource($this->whenLoaded('product')),

            // Quantity and Pricing
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'formatted_unit_price' => '$' . number_format($this->unit_price, 2),
            'formatted_total_price' => '$' . number_format($this->total_price, 2),

            // Design Specifications
            'design_specifications' => $this->design_specifications,
            'dimensions' => $this->dimensions,
//            'print_dimensions' => $this->print_dimensions,
            'color_options' => $this->color_options,
            'special_instructions' => $this->special_instructions,

            // Gang Sheet Information
//            'gang_sheet_position' => $this->gang_sheet_position,
//            'is_part_of_gang_sheet' => $this->isPartOfGangSheet(),

            // Design Files
            'design_files' => OrderItemDesignFileResource::collection($this->whenLoaded('designFiles')),
            'design_files_count' => $this->whenCounted('designFiles') ?? $this->design_files_count,
            'has_design_files' => $this->hasDesignFiles(),
            'design_files_urls' => $this->when($this->relationLoaded('designFiles'), $this->design_files_urls),

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
