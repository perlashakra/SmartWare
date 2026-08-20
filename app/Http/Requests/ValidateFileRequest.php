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
            'facility_id' =>['required', 'exists:facilities,id'],
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ];
    }
}
