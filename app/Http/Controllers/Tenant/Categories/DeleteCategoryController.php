<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Categories;

use App\Actions\Tenant\Categories\DeleteCategoryAction;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Category;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Catálogo', 'Categorias e produtos da loja do tenant.', weight: 14)]
final class DeleteCategoryController extends Controller
{
    /**
     * Excluir categoria
     *
     * Realiza a exclusão lógica (soft delete) da categoria. Os produtos vinculados a ela não são excluídos, mas ficam sem categoria.
     *
     * Requer a permission `categories.delete`.
     */
    public function __invoke(Category $category, DeleteCategoryAction $action): Response
    {
        Gate::authorize('delete', $category);

        $action->execute($category);

        return response()->noContent();
    }
}
