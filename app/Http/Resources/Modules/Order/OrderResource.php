<?php

namespace App\Http\Resources\Modules\Order;

use App\Http\Resources\Lookups\UserLookupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
//            'status' => $this->status,
//            'status_label' => $this->status_label ?? $this->status,
            'status' => [
                'value' => $this->status->value ?? null,
                'name' => $this->status->name ?? null,
            ],
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,

            // Financial Information
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'shipping_amount' => $this->shipping_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'formatted_subtotal' => '$' . number_format($this->subtotal, 2),
            'formatted_tax_amount' => '$' . number_format($this->tax_amount, 2),
            'formatted_shipping_amount' => '$' . number_format($this->shipping_amount, 2),
            'formatted_discount_amount' => '$' . number_format($this->discount_amount, 2),
            'formatted_total_amount' => '$' . number_format($this->total_amount, 2),

            // Customer Information
            'customer' => new UserLookupResource($this->whenLoaded('user')),

            // Addresses
            'billing_address' => $this->billing_address,
            'shipping_address' => $this->shipping_address,

            // Order Details
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->whenCounted('items'),
            'total_quantity' => $this->items->sum('quantity') ?? null,

            // Gang Sheet Information
//            'is_gang_sheet' => $this->is_gang_sheet,
//            'gang_sheet_data' => $this->gang_sheet_data,

            // Additional Information
            'notes' => $this->notes,
            'special_instructions' => $this->special_instructions,
            'estimated_delivery_date' => $this->estimated_delivery_date,
            'tracking_number' => $this->tracking_number,
//            'tracking_url' => $this->tracking_number ? $this->getTrackingUrl() : null,

            // Status History
            'status_history' => OrderStatusHistoryResource::collection($this->whenLoaded('statusHistory')),
            'last_status_change' => $this->whenLoaded('statusHistory', function() {
                return $this->statusHistory->first() ? new OrderStatusHistoryResource($this->statusHistory->first()) : null;
            }),

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_human' => $this->created_at->diffForHumans(),
            'updated_at_human' => $this->updated_at->diffForHumans(),

            // Additional Computed Fields
            'can_be_cancelled' => $this->canBeCancelled(),
//            'can_be_refunded' => $this->canBeRefunded(),
            'is_paid' => $this->payment_status === 'paid',
            'payment_intent_id' => $this->when($this->payment_intent_id, $this->payment_intent_id),

            // File Information
            'has_design_files' => $this->items->some(fn($item) => $item->hasDesignFiles()),
            'total_design_files' => $this->items->sum(fn($item) => $item->design_files_count),
        ];
    }
}
