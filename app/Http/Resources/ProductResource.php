<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $request->user()->language_preference === 'ar' ? $this->name_ar : $this->name_en,
            'price' => $this->price,
            'unit' => __('unit.'.$this->unit),
            'company' => new CompanyResource($this->whenLoaded('company')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'description' => $request->user()->language_preference === 'ar' ? $this->description_ar : $this->description_en,
            'product_image' => $this->product_image ? Storage::url($this->product_image) : null,
        ];
    }
}
