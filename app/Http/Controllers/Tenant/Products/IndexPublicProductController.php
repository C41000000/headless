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

#[Group('Tenant - Catálogo', 'Categorias e produtos da loja do tenant.', weight: 14)]
final class IndexPublicProductController extends Controller
{
    /**
     * Listar produtos públicos ativos
     *
     * Retorna a lista paginada de produtos ativos da loja vitrine, com categoria vinculada.
     * Suporta filtros por `category_slug`, `featured` e busca por nome ou descrição via `search`.
     */
    #[QueryParameter('page', 'Número da página.', type: 'int', default: 1)]
    #[QueryParameter('category_slug', 'Filtrar produtos por slug da categoria.', type: 'string')]
    #[QueryParameter('featured', 'Filtrar apenas produtos em destaque.', type: 'boolean')]
    #[QueryParameter('search', 'Buscar produtos por nome ou descrição.', type: 'string')]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $query = Product::query()
            ->active()
            ->with('category');

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($request->filled('category_slug')) {
            $categorySlug = (string) $request->query('category_slug');
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $categorySlug));
        }

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->where(function (Builder $q) use ($search) {
                $q->whereLike('name', "%{$search}%")
                    ->orWhereLike('description', "%{$search}%");
            });
        }

        return ProductResource::collection(
            $query->latest()->paginate(12)->withQueryString()
        );
    }
}
