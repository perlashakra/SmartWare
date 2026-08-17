<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidateFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' =>['required', 'exists:sections,id'],
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ];
    }
}
