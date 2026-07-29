<?php

namespace App\Http\Requests;

use App\Enums\CategoryEnum;
use App\Enums\ProductType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'exists:companies,id'],
            'sku' => ['required', 'string', Rule::unique('products', 'sku'), 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'container_type' => 'required|string',
            'categories' => 'required|array',
            'categories.*' => ['required', 'string', Rule::enum(CategoryEnum::class)],
            'product_type' => ['required', 'string', Rule::enum(ProductType::class)],
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ];
    }

    public function messages(){
        return [
            
        ];
    }
}
