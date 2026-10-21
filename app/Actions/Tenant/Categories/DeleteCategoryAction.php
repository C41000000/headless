<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Categories;

use App\Models\Tenant\Category;

final readonly class DeleteCategoryAction
{
    public function execute(Category $category): void
    {
        $category->delete();
    }
}
