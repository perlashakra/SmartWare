<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
{   
    public function toArray(Request $request): array {
        return [
            'facility_id' => $this->id,
            'facility_name' =>  $request->user()->language_preference === 'ar' ? $this->facility_name_ar : $this->facility_name_en,
            'facility_type' => __('facility_type.'.$this->facility_type->value),
            'facility_status' => $this->facility_status,
            'owner_id' => $this->user_id,
            'address_id' => $this->address_id,
        ];
    }
}
