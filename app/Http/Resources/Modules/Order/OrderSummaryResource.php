<?php

namespace App\Http\Resources\Modules\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_name' => $this->user->name ?? 'Guest',
            'customer_email' => $this->user->email ?? $this->billing_address['email'] ?? null,
            'status' => $this->status,
            'status_label' => $this->status_label ?? $this->status,
            'total_amount' => $this->total_amount,
            'formatted_total_amount' => '$' . number_format($this->total_amount, 2),
            'items_count' => $this->items_count ?? $this->items->count(),
            'is_gang_sheet' => $this->is_gang_sheet,
            'payment_status' => $this->payment_status,
            'created_at' => $this->created_at,
            'created_at_human' => $this->created_at->diffForHumans(),
            'estimated_delivery_date' => $this->estimated_delivery_date,
            'urgency_level' => $this->getUrgencyLevel(),
        ];
    }

    /**
     * Get urgency level based on delivery date and status
     */
    private function getUrgencyLevel(): string
    {
        if (!$this->estimated_delivery_date || in_array($this->status, ['delivered', 'cancelled'])) {
            return 'normal';
        }

        $daysUntilDelivery = now()->diffInDays($this->estimated_delivery_date, false);

        if ($daysUntilDelivery < 0) {
            return 'overdue';
        } elseif ($daysUntilDelivery <= 2) {
            return 'urgent';
        } elseif ($daysUntilDelivery <= 5) {
            return 'high';
        } else {
            return 'normal';
        }
    }
}
