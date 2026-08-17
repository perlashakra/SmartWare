<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AddItemCartRequest extends FormRequest
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
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:facilities,id'],
            'facility_id' => [
                'required',
                Rule::exists('facilities', 'id')
                    ->where(fn ($query) => $query
                        ->where('user_id', Auth::id())
                        ->where('facility_type', 'business')
                    ),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
