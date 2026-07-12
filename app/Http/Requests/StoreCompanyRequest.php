<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
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
            'name' => ['required','string', 'max:255', Rule::unique('companies', 'name')->ignore($this->id)],
            'phone' => ['required', 'digits:10', Rule::unique('companies', 'phone')->ignore($this->id)],
            'email' => ['required|string|email|max:255', Rule::unique('companies', 'email')->ignore($this->id)],
            'website' => ['required', 'string', Rule::unique('companies', 'website')->ignore($this->id)],
        ];
    }
}
