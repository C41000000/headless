<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Categories\CategoryResource;
use App\Models\Tenant\Category;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Catálogo', 'Categorias e produtos da loja do tenant.', weight: 14)]
final class ShowCategoryController extends Controller
{
    /**
     * Detalhes de uma categoria
     *
     * Retorna os detalhes de uma categoria específica com a contagem de produtos associados.
     *
     * Requer a permission `categories.view`.
     */
    public function __invoke(Category $category): CategoryResource
    {
        Gate::authorize('view', $category);

        $category->loadCount('products');

        return new CategoryResource($category);
    }
}
