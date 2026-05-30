<?php

namespace App\Http\Requests;

use App\Models\EmployeeAnnouncement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class RegisterWorkerRequest extends FormRequest
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
            'national_id' => 'required|digits:11|exists:employee_announcements,national_id',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone_number' => 'required|digits:10|unique:users,phone_number',
            'password' => 'required|string|min:10',
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
            'email.unique' => __('validation.email_already_exists'),
            'phone_number.required' => __('validation.phone_number_required'),
            'phone_number.digits' => __('validation.phone_number_digits'),
            'phone_number.unique' => __('validation.phone_number_already_exists'),
            'password.required' => __('validation.password_required'),
            'password.min' => __('validation.password_min'),
            'password.confirmed' => __('validation.password_confirmed'),
            'role.required' => __('validation.role_required'),
            'role.in' => __('validation.role_in_worker'),
            'language_preference.in' => __('validation.language_preference_in'),
        ];
    }
}
