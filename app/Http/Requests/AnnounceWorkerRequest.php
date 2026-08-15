<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AnnounceWorkerRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'national_id' => ['required', 'digits:11', 'unique:employee_announcements,national_id'],
            'facility_id' => ['required', 'exists:facilities,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'national_id.required' => 'National ID is required.',
            'national_id.digits' => 'National ID is invalid.',
            'national_id.unique' => 'National ID already exists.',
            'facility_id.required' => 'Facility is required.',
            'facility_id.digits' => 'Facility is invalid.',
        ];
    }
}
