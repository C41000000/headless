<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Categories;

use App\DTOs\Tenant\Categories\CreateCategoryDTO;
use App\Models\Tenant\Category;

final readonly class CreateCategoryAction
{
    public function execute(CreateCategoryDTO $dto): Category
    {
        return Category::query()->create($dto->toArray());
    }
}
