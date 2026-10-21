<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Categories;

use App\Actions\Tenant\Categories\CreateCategoryAction;
use App\DTOs\Tenant\Categories\CreateCategoryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Categories\StoreCategoryRequest;
use App\Http\Resources\Tenant\Categories\CategoryResource;
use App\Models\Tenant\Category;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Catálogo', 'Categorias e produtos da loja do tenant.', weight: 14)]
final class StoreCategoryController extends Controller
{
    /**
     * Criar nova categoria
     *
     * Cadastra uma nova categoria no catálogo do tenant.
     *
     * Requer a permission `categories.create`.
     */
    public function __invoke(StoreCategoryRequest $request, CreateCategoryAction $action): CategoryResource
    {
        Gate::authorize('create', Category::class);

        $category = $action->execute(CreateCategoryDTO::from($request->validated()));

        /** @status 201 */
        return new CategoryResource($category);
    }
}
