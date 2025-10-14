<?php

namespace App\Http\Resources\Lookups;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductLookupResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'price' => $this->price,
            'formatted_price' => '$' . number_format($this->price, 2),
            'primary_image_url' => $this->primary_image_url ?? null,
            'is_available' => $this->isAvailable(),
            'category' => new CategoryLookupResource($this->whenLoaded('category')),
            'short_description' => $this->short_description ?? null,
        ];
    }
}
