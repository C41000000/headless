<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Categories\CategoryResource;
use App\Models\Tenant\Category;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Catálogo', 'Categorias e produtos da loja do tenant.', weight: 14)]
final class IndexCategoryController extends Controller
{
    /**
     * Listar todas as categorias (Painel Administrativo)
     *
     * Retorna a listagem de categorias cadastradas com suporte a filtro por status e busca por nome.
     *
     * Requer a permission `categories.view`.
     */
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Category::class);

        $query = Category::query()
            ->ordered()
            ->withCount('products');

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->whereLike('name', "%{$search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return CategoryResource::collection($query->get());
    }
}
