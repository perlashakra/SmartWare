<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', Rule::exists('facilities', 'id')->where('facility_type', 'warehouse')],
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:0',
        ];
    }
}
