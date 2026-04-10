<?php

namespace App\Http\Resources\Modules\Order;

use App\Http\Resources\GangsheetResource;
use App\Http\Resources\Lookups\ProductLookupResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'type' => $this->type,

            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'formatted_unit_price' => '$' . number_format($this->unit_price, 2),
            'formatted_total_price' => '$' . number_format($this->total_price, 2),
        ];

        if ($this->type === 'product' && $this->product_id) {
            $data['product_id'] = $this->product_id;
            $data['product_name'] = $this->product_name;
            $data['product_sku'] = $this->product_sku;
            $data['dimensions'] = $this->dimensions;
            $data['color_options'] = $this->color_options;
            $data['size_options'] = $this->size_options;
            $data['special_instructions'] = $this->special_instructions;
            $data['design_specifications'] = $this->design_specifications;
            $data['product'] = new ProductLookupResource($this->whenLoaded('product'));

            // Design Files (only for product items)
            $data['design_files'] = OrderItemDesignFileResource::collection($this->whenLoaded('designFiles'));
            $data['design_files_count'] = $this->whenCounted('designFiles') ?? $this->design_files_count;
            $data['has_design_files'] = $this->hasDesignFiles();
            $data['design_files_urls'] = $this->when($this->relationLoaded('designFiles'), $this->design_files_urls);
        }

        if ($this->type === 'gangsheet' && $this->gangsheet_id) {
            $user = $request->user();
            $isSuperAdmin = $user && $user->level === \App\Models\User::LEVEL_SUPER_ADMIN;

            $data['gangsheet_id'] = $this->gangsheet_id;
            $data['is_gangsheet'] = true;

            if ($isSuperAdmin) {
                // Full details for super admin
                $data['gangsheet'] = new GangsheetResource($this->whenLoaded('gangsheet'));
                $data['gangsheet_ready'] = $this->when(
                    $this->relationLoaded('gangsheet') && $this->gangsheet,
                    $this->gangsheet?->isReadyForDownload() ?? false
                );
            } else {
                // Limited info for others
                $data['gangsheet'] = $this->when(
                    $this->relationLoaded('gangsheet') && $this->gangsheet,
                    [
                        'status' => $this->gangsheet?->status,
                        'is_ready' => $this->gangsheet?->isReadyForDownload() ?? false,
                        'name' => $this->gangsheet?->name,
                    ]
                );
            }
        }

        return $data;
    }
}
