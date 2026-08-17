<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterClientRequest extends FormRequest
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
            'first_name' => 'required|string|max:25',
            'last_name' => 'required|string|max:25',
            'email' => 'required|string|email|max:255',
            'phone_number' => 'required|digits:10',
            'password' => 'required|string|min:10',
            'role' => 'required|string|in:client',
        ];
    }

    public function messages(): array
    {
        return[
            'first_name.required' => __('validation.first_name_required'),
            'first_name.string' => __('validation.first_name_string'),
            'first_name.max' => __('validation.first_name_max'),
            'last_name.required' => __('validation.last_name_required'),
            'last_name.string' => __('validation.last_name_string'),
            'last_name.max' => __('validation.last_name_max'),
            'national_id.required' => __('validation.national_id_required'),
            'national_id.string' => __('validation.national_string'),
            'national_id.exists' => __('validation.national_id_not_found'),
            'birthday.required' => __('validation.birthday_required'),
            'birthday.date' => __('validation.birthday_date'),
            'birthday.before_or_equal' => __('validation.birthday_before_or_equal'),
            'email.required' => __('validation.email_required'),
            'email.string' => __('validation.email_string'),
            'email.email' => __('validation.email_email'),
            'email.max' => __('validation.email_max'),
            'phone_number.required' => __('validation.phone_number_required'),
            'phone_number.digits' => __('validation.phone_number_digits'),
            'password.required' => __('validation.password_required'),
            'password.min' => __('validation.password_min'),
            'password.confirmed' => __('validation.password_confirmed'),
            'role.required' => __('validation.role_required'),
            'role.in' => __('validation.role_in_client'),
            'language_preference.in' => __('validation.language_preference_in'),
            'business_name.required' => __('validation.business_name_required'),
            'business_name.string' => __('validation.business_name_string'),
            'business_name.max' => __('validation.business_name_max'),
        ];
    }
}
