<?php

declare(strict_types=1);

namespace App\DTOs\Tenant\Categories;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateCategoryDTO extends Data
{
    public function __construct(
        public readonly string|Optional $name,
        public readonly string|Optional $slug,
        public readonly string|null|Optional $description,
        public readonly bool|Optional $is_active,
        public readonly int|Optional $order,
    ) {}
}
