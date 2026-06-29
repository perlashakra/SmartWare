<?php

namespace App\Http\Requests;

use App\Enums\BusinessTypeEnum;
use App\Enums\ProductType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePreferencesRequest extends FormRequest
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
            'role' => ['required', 'string', Rule::in(['client', 'warehouse_manager'])],
            'business_type' => ['required_if:role,client', 'nullable', Rule::enum(BusinessTypeEnum::class)],
            'product_types' => ['required', 'array', 'min:1'],
            'product_types.*' => [Rule::enum(ProductType::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Please specify if you are a client or a warehouse manager.',
            'business_type.required_if' => 'Clients must select a business type.',
            'product_types.required' => 'You must select at least one product type preference.',
        ];
    }
}
