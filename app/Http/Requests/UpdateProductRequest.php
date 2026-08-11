<?php

namespace App\Http\Requests;

use App\Enums\ContainerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['sometimes', 'exists:companies,id'],
            'sku' => ['sometimes', 'string', Rule::unique('products', 'sku')->ignore($this->product), 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
            'name_en' => 'sometimes|string|max:255',
            'name_ar' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'container_type' => ['sometimes', Rule::enum(ContainerType::class)],
            'categories' => 'sometimes|array',
            'categories.*' => ['required', 'string', 'exists:categories,id'],
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ];
    }
}
