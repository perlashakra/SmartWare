<?php

namespace App\Http\Requests;

use App\Enums\FacilityType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFacilityRequest extends FormRequest
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
            'facility_name' => 'required|string',
            'facility_type' => ['required', Rule::enum(FacilityType::class)],
            'facility_status' => 'required|in:pending,submitted,approved,rejected',
            'address_id' => 'required|exists:addresses,id',
        ];
    }
}
