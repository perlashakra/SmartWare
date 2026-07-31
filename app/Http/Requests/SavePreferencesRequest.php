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
            'role' => ['required', 'string', Rule::in(['client', 'warehouse_manager'])],
            'business_type' => ['required_if:role,client', 'nullable', Rule::enum(BusinessTypeEnum::class)],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => [Rule::enum(CategoryEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => __('onboarding.role_required'),
            'business_type.required_if' => __('onboarding.business_type_required_if_client'),
            'categories.required' => __('onboarding.categories_required'),
        ];
    }
}
