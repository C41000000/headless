<?php

declare(strict_types=1);

namespace App\DTOs\Tenant\Products;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateProductDTO extends Data
{
    public function __construct(
        public readonly string|Optional $name,
        public readonly string|Optional $slug,
        public readonly float|Optional $price,
        public readonly string|null|Optional $description,
        public readonly float|null|Optional $promotional_price,
        public readonly int|Optional $stock,
        public readonly string|null|Optional $sku,
        public readonly string|null|Optional $image_url,
        public readonly bool|Optional $is_active,
        public readonly bool|Optional $is_featured,
        public readonly int|null|Optional $category_id,
    ) {}
}
