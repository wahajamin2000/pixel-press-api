<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryResource extends JsonResource
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
            'products_count' => $this->products_count ?? 0,
            "status" => [
                'value' => $this->status ?? null,
                'name' => $this->statusName ?? null,
            ],

            'created_by' => $this->when($request->user()?->isSuperAdmin(), optional($this->createdBy)->name) ?? null,
            'updated_by' => $this->when($request->user()?->isSuperAdmin(), optional($this->updatedBy)->name) ?? null,

            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null,
//            'deleted_at' => $this->when($request->user()?->isSuperAdmin(), $this->deleted_at) ?? null,
        ];
    }
}
