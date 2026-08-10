<?php

namespace App\Http\Requests;

use App\Enums\BusinessTypeEnum;
use App\Enums\CategoryEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'facility_id' => ['nullable', 'integer', 'exists:facilities,id'],
            'facility_name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', Rule::in(['client', 'warehouse_manager', 'warehouse_admin'])],
            'business_type' => ['required_if:role,client', 'nullable', Rule::enum(BusinessTypeEnum::class)],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => [Rule::enum(CategoryEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'facility_id.integer' => __(),
            'facility_id.exists' => __(),
            'role.required' => __('onboarding.role_required'),
            'business_type.required_if' => __('onboarding.business_type_required_if_client'),
            'categories.required' => __('onboarding.categories_required'),
        ];
    }
}
