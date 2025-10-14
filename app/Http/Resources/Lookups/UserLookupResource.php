<?php

namespace App\Http\Resources\Lookups;

use App\Models\Modules\Friendship;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLookupResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id ?? null,
            "name" => $this->name ?? null,
            "image" => $this->pic,
        ];
    }
}
