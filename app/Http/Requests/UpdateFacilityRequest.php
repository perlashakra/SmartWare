<?php

namespace App\Http\Requests;

use App\Enums\FacilityType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFacilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'facility_name' => 'sometimes|required',
            'facility_type' => ['sometimes', Rule::enum(FacilityType::class)],
            'facility_status' => 'sometimes|in:pending,submitted,approved,rejected',
            'address_id' => 'sometimes|exists:addresses,id',
        ];
    }
}
