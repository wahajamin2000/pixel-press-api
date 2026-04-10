<?php

namespace App\Http\Resources\Modules\Order;

use App\Http\Resources\Lookups\UserLookupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'label' => $this->label,
            'value' => $this->value,
            'type' => $this->type,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];
    }
}
