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
            'unit' => $this->unit,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'description' => $request->user()->language_preference === 'ar' ? $this->description_ar : $this->description_en,
            'product_image' => $this->product_image ? Storage::url($this->product_image) : null,
        ];
    }
}
