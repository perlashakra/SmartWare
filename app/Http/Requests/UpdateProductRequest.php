<?php

namespace App\Http\Requests;

use App\Enums\UnitEnum;
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
            'sku' => ['sometimes', 'string', Rule::unique('products', 'sku')->ignore($this->product), 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
            'name_en' => 'sometimes|string|max:255',
            'name_ar' => 'sometimes|string|max:255',
            'unit' => ['sometimes', Rule::enum(UnitEnum::class)],
            'unit_price' => 'sometimes|numeric|min:0',
            'description_en' => 'sometimes|string',
            'description_ar' => 'sometimes|string',
            'product_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            //'warehouse_id' => ['required','exists:facilities,id'],
            'quantity' => 'sometimes|numeric|min:0',
        ];
    }
}
