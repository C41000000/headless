<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Products\ProductResource;
use App\Models\Tenant\Product;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Catálogo', 'Categorias e produtos da loja do tenant.', weight: 14)]
final class IndexProductController extends Controller
{
    /**
     * Listar todos os produtos (Painel Administrativo)
     *
     * Retorna a listagem paginada de produtos com categoria vinculada, filtros por status, destaque e busca.
     *
     * Requer a permission `products.view`.
     */
    #[QueryParameter('page', 'Número da página.', type: 'int', default: 1)]
    #[QueryParameter('search', 'Buscar por nome, SKU ou descrição.', type: 'string')]
    #[QueryParameter('category_uuid', 'Filtrar por UUID da categoria.', type: 'string')]
    #[QueryParameter('is_active', 'Filtrar por status ativo/inativo.', type: 'boolean')]
    #[QueryParameter('is_featured', 'Filtrar por destaque.', type: 'boolean')]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Product::class);

        $query = Product::query()->with('category');

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->where(function (Builder $q) use ($search) {
                $q->whereLike('name', "%{$search}%")
                    ->orWhereLike('sku', "%{$search}%")
                    ->orWhereLike('description', "%{$search}%");
            });
        }

        if ($request->filled('category_uuid')) {
            $categoryUuid = (string) $request->query('category_uuid');
            $query->whereHas('category', fn (Builder $q) => $q->where('uuid', $categoryUuid));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        return ProductResource::collection(
            $query->latest()->paginate(15)->withQueryString()
        );
    }
}
