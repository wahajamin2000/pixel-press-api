<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class NotificationResource extends JsonResource
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
            "user_id" => (int)$this->user_id ?? null,
            "type" => [
                'name' => $this->typeName,
                'value' => $this->type,
            ],
            "title" => $this->title ?? null,
            "message" => $this->message ?? null,
            "is_read" => (int)$this->is_read ?? null,
        ];
    }


}
