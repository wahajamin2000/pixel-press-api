<?php

namespace App\Http\Resources\Lookups;

use Illuminate\Http\Resources\Json\JsonResource;

class TaxExemptUserLookupResource extends JsonResource
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
            'document_url' => $this->tax_exempt_document_url ?? null,
        ];
    }
}
