<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\Lookups\ReviewLookupResource;
use App\Http\Resources\Modules\BusinessTypeResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
class ProfileResource extends JsonResource
{
    private $data;

    public function __construct($resource, array $data = null)
    {
        parent::__construct($resource);
        $this->data = $data;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            "id" => $this->id ?? null,
            "general" => [
                "full_name" => $this->name ?? null,
                "first_name" => $this->first_name ?? null,
                "last_name" => $this->last_name ?? null,
                "email" => $this->email ?? null,
                "gender" => [
                    'value' => $this->gender ?? null,
                    'name' => $this->genderName ?? null,
                ],
            ],
            "address" => [
                "fullAddress" => $this->address ?? null,
                "address_line_one" => $this->address_line_one ?? null,
                "address_line_two" => $this->address_line_two ?? null,
                "city" => $this->city ?? null,
                "state" => $this->state ?? null,
                "post_code" => $this->post_code ?? null,
            ],
            "role" => $this->role ?? null,
            "level" => $this->level ? (int)$this->level : null,
            "status" => [
                'value' => $this->status ?? null,
                'name' => $this->statusName ?? null,
            ],
            "image" => $this->pic,
            'created_at' => $this->created_at ? [
                'date' => $this->created_at->format('Y-m-d'),
                'time' => $this->created_at->format('H:i'),
                'formatted' => $this->created_at->format('Y-m-d H:i:s'),
                'display_date' => $this->created_at->format('M d, Y'),
                'display_time' => $this->created_at->format('g:i A'),
                'day_of_week' => $this->created_at->format('l'),
                'is_today' => $this->created_at->isToday(),
                'is_tomorrow' => $this->created_at->isTomorrow(),
                'is_yesterday' => $this->created_at->isYesterday(),
                'human_readable' => $this->created_at->diffForHumans(),
            ] : null
        ];

    }
}
