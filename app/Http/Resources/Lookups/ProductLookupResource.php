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
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'type' => [
                'value' => $this->type->value ?? null,
                'name' => $this->type->name ?? null,
            ],
            'price' => $this->price,
            'formatted_price' => '$' . number_format($this->price, 2),
            'primary_image_url' => $this->primary_image_url ?? null,
            'is_available' => $this->isAvailable(),
            'category' => new CategoryLookupResource($this->whenLoaded('category')),
            'short_description' => $this->short_description ?? null,
        ];

        if (($this->type->value ?? null) === 'custom') {
            $data['color_options'] = $this->color_options ?? [];
            $data['size_options'] = $this->size_options ?? [];
            $data['price_per_sqin'] = $this->price_per_sqin ?? null;
        }

        return $data;
    }
}
