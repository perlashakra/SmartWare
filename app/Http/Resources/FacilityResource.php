<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'facility_name' => $this->facility_name,
            'facility_type' => $this->facility_type,
            'facility_status' => $this->facility_status,
            'owner_id' => $this->user_id,
            //'address_id' => $this->address_id,
        ];
    }
}
