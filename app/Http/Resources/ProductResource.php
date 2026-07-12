<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
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
        ];
    }
}
