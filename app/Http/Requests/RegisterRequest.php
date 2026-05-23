<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class RegisterRequest extends FormRequest
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
        $acceptableDate = Carbon::today()->subYears(18)->format('Y-m-d');
        $rules = [
            'first_name' => 'required|string|max:25',
            'last_name' => 'required|string|max:25',
            'birthday' => 'required|date|before_or_equal:' . $acceptableDate,
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone_number' => 'required|digits:10|unique:users,phone_number',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required',
            'language_preference' => 'nullable|string|in:en,ar',
        ];
        return $rules;
    }

    public function messages(): array
    {
        return [

        ];
    }
}
