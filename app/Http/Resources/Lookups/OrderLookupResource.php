<?php

namespace App\Http\Resources\Lookups;

use App\Http\Resources\Modules\Order\OrderItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderLookupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'order_number' => $this->order_number,

            // Customer Information
            'customer' => new UserLookupResource($this->whenLoaded('user')),

            'status' => [
                'value' => $this->status->value ?? null,
                'name' => $this->status->name ?? null,
            ],
            'payment_status' => $this->payment_status,

            'formatted_subtotal' => '$' . number_format($this->subtotal, 2),
            'formatted_discount_amount' => '$' . number_format($this->discount_amount, 2),
            'formatted_total_amount' => '$' . number_format($this->total_amount, 2),

            'items_count' => $this->whenCounted('items'),
            'total_quantity' => $this->items->sum('quantity') ?? null,

            'tracking_number' => $this->tracking_number,

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_human' => $this->created_at->diffForHumans(),
            'updated_at_human' => $this->updated_at->diffForHumans(),

            // Additional Computed Fields
            'can_be_cancelled' => $this->canBeCancelled(),
//            'can_be_refunded' => $this->canBeRefunded(),
            'is_paid' => $this->payment_status === 'paid',
            // File Information
            'total_design_files' => $this->items->sum(fn($item) => $item->design_files_count),
        ];
    }
}
