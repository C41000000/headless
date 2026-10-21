<?php

declare(strict_types=1);

namespace App\DTOs\Tenant\Categories;

use Spatie\LaravelData\Data;

final class CreateCategoryDTO extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description = null,
        public readonly bool $is_active = true,
        public readonly int $order = 0,
    ) {}
}
