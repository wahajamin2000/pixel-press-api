<?php

namespace App\Http\Resources\Modules\Order;

use App\Http\Resources\Lookups\UserLookupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'from_status_label' => $this->from_status_label ?? $this->from_status,
            'to_status_label' => $this->to_status_label ?? $this->to_status,
            'notes' => $this->notes,
            'changed_by' => new UserLookupResource($this->whenLoaded('changedByUser')),
            'changed_at' => $this->changed_at,
            'changed_at_human' => $this->changed_at->diffForHumans(),
        ];
    }
}
