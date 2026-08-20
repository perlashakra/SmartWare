<?php

namespace App\Http\Requests;

use App\Enums\FacilityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'facility_name_en' => 'required_without:facility_name_ar|string',
            'facility_name_ar' => 'required_without:facility_name_en|string',
            'facility_type' => ['required', Rule::enum(FacilityType::class)],
        ];
    }
}
