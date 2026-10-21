<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Products\ProductResource;
use App\Models\Tenant\Product;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Tenant - Catálogo', 'Categorias e produtos da loja do tenant.', weight: 14)]
final class ShowPublicProductController extends Controller
{
    /**
     * Detalhes públicos de um produto
     *
     * Retorna os dados completos do produto pelo slug informado para visualização na vitrine.
     */
    public function __invoke(Request $request, string $slug): ProductResource
    {
        $product = Product::query()
            ->active()
            ->where('slug', $slug)
            ->with('category')
            ->firstOrFail();

        return new ProductResource($product);
    }
}
