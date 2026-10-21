<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Categories;

use App\DTOs\Tenant\Categories\UpdateCategoryDTO;
use App\Models\Tenant\Category;

final readonly class UpdateCategoryAction
{
    public function execute(Category $category, UpdateCategoryDTO $dto): Category
    {
        $category->update($dto->toArray());

        return $category->fresh();
    }
}
