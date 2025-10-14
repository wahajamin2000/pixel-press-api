<?php

namespace App\Http\Resources\Modules\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderCollection extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'has_more_pages' => $this->hasMorePages(),
            ],
            'statistics' => $this->when($request->get('include_stats'), [
                'total_orders' => $this->collection->count(),
                'total_amount' => '$' . number_format($this->collection->sum('total_amount'), 2),
                'average_order_value' => '$' . number_format($this->collection->avg('total_amount'), 2),
                'status_breakdown' => $this->collection->groupBy('status')->map->count(),
                'payment_status_breakdown' => $this->collection->groupBy('payment_status')->map->count(),
            ]),
        ];
    }
}
