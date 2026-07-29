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
            'name' => $this->name,
            'price' => $this->price,
            'container_type' => $this->container_type,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'product_image' => $this->product_image ? Storage::url($this->product_image) : null,
        ];
    }
}
