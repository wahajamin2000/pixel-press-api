<?php

namespace App\Http\Resources;

use App\Http\Resources\Lookups\CategoryLookupResource;
use App\Http\Resources\Lookups\ProductLookupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?? null,
            'short_description' => $this->short_description ?? null,
            'sku' => $this->sku,
            'price' => $this->price ?? null,
            'formatted_price' => $this->formatted_price ?? null,
            'base_price' => $this->base_price ?? null,
            'formatted_base_price' => $this->formatted_base_price ?? null,
            'weight' => $this->weight ?? null,
//            'dimensions' => $this->dimensions ?? [],
            'material' => $this->material ?? null,
//            'color_options' => $this->color_options?? [],
//            'size_options' => $this->size_options ?? [],
            'category' => new CategoryLookupResource($this->whenLoaded('category')) ?? null,
            'status' => [
                'value' => $this->status->value ?? null,
                'name' => $this->status->name ?? null,
            ],
            'is_featured' => $this->is_featured ?? null,
            'meta_title' => $this->meta_title ?? null,
            'meta_description' => $this->meta_description ?? null,
//            'sort_order' => $this->sort_order ?? null,
            'specifications' => $this->specifications ?? null,
//            'print_areas' => $this->print_areas ?? null,

            // Images
            'primary_image_url' => $this->primary_image_url ?? null,
//            'images' => ProductImageResource::collection($this->whenLoaded('images')) ?? null,
            'all_images_urls' => $this->whenLoaded('images', function () {
                return $this->all_images_urls;
            }),

            // Computed properties
            'is_available' => $this->isAvailable() ?? null,
            'is_out_of_stock' => $this->isOutOfStock() ?? null,

            'created_by' => $this->when($request->user()?->isSuperAdmin(), optional($this->createdBy)->name) ?? null,
            'updated_by' => $this->when($request->user()?->isSuperAdmin(), optional($this->updatedBy)->name) ?? null,

            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null,
            'deleted_at' => $this->when($request->user()?->isSuperAdmin(), $this->deleted_at) ?? null,
        ];
    }
}
