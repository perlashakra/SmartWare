<?php

namespace App\Http\Requests;

use App\Enums\CategoryEnum;
use App\Enums\ProductType;
use Illuminate\Contracts\Validation\ValidationRule;
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
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'container_type' => 'sometimes|string',
            'categories' => 'sometimes|array',
            'categories.*' => ['required', 'string', Rule::enum(CategoryEnum::class)],
            'product_type' => ['sometimes', 'string', Rule::enum(ProductType::class)],
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ];
    }

    public function messages(){
        return [];
    }
}
