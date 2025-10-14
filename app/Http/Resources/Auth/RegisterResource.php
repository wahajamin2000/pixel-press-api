<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\Lookups\ReviewLookupResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RegisterResource extends JsonResource
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
            'last_login' => isset($this->last_login) ? $this->last_login->format('Y-m-d h:i:s A') : null,
            "jwt_token" => $this->data['jwt_token'] ?? null,
        ];
    }
}
