<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Categories;

use App\Actions\Tenant\Categories\UpdateCategoryAction;
use App\DTOs\Tenant\Categories\UpdateCategoryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Categories\UpdateCategoryRequest;
use App\Http\Resources\Tenant\Categories\CategoryResource;
use App\Models\Tenant\Category;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Catálogo', 'Categorias e produtos da loja do tenant.', weight: 14)]
final class UpdateCategoryController extends Controller
{
    /**
     * Atualizar categoria
     *
     * Atualiza os dados de uma categoria (nome, slug, descrição, status ou ordenação).
     *
     * Requer a permission `categories.update`.
     */
    public function __invoke(UpdateCategoryRequest $request, Category $category, UpdateCategoryAction $action): CategoryResource
    {
        Gate::authorize('update', $category);

        $updated = $action->execute($category, UpdateCategoryDTO::from($request->validated()));

        return new CategoryResource($updated);
    }
}
