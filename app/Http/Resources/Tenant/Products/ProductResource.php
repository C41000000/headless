<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant\Products;

use App\Http\Resources\Tenant\Categories\CategoryResource;
use App\Models\Tenant\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'promotional_price' => $this->promotional_price !== null ? (float) $this->promotional_price : null,
            'stock' => $this->stock,
            'sku' => $this->sku,
            'image_url' => $this->image_url,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'category_uuid' => $this->category?->uuid,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
