<?php

namespace App\Http\Requests;

use App\Enums\UnitEnum;
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
            'sku' => ['required', 'string', Rule::unique('products', 'sku'), 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
            'name_en' => 'required_without:name_ar|string|max:255',
            'name_ar' => 'required_without:name_en|string|max:255',
            'unit' => ['nullable', Rule::enum(UnitEnum::class)],
            'categories' => 'required|array',
            'categories.*' => ['required', 'integer', 'exists:categories,id'],
            'company_name_en' => ['nullable', 'string'],
            'company_name_ar' => ['nullable', 'string'],
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ];
    }
}
