<?php

declare(strict_types=1);

namespace App\DTOs\Tenant\Products;

use Spatie\LaravelData\Data;

final class CreateProductDTO extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly float $price,
        public readonly ?string $description = null,
        public readonly ?float $promotional_price = null,
        public readonly int $stock = 0,
        public readonly ?string $sku = null,
        public readonly ?string $image_url = null,
        public readonly bool $is_active = true,
        public readonly bool $is_featured = false,
        public readonly ?int $category_id = null,
    ) {}
}
