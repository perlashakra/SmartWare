<?php

namespace App\Http\Requests;

use App\Enums\ContainerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
            'name_en' => 'required_without:name_ar|string|max:255',
            'name_ar' => 'required_without:name_en|string|max:255',
            'price' => 'required|numeric|min:0',
            'container_type' => ['required', Rule::enum(ContainerType::class)],
            'categories' => 'required|array',
            'categories.*' => ['required', 'integer', 'exists:categories,id'],
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ];
    }
}
