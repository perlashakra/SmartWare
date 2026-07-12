<?php

namespace App\Http\Requests;

use App\Enums\ContainerType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
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
            'company_id' => ['required', 'exists:companies,id'],
            'sku' => ['required', 'string', Rule::unique('products', 'sku'), 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'container_type' => ['required', 'string', Rule::enum(ContainerType::class)],
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
        ];
    }

    public function messages(){
        return [
            
        ];
    }
}
