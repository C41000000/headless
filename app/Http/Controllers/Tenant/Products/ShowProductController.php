<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Products\ProductResource;
use App\Models\Tenant\Product;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Catálogo', 'Categorias e produtos da loja do tenant.', weight: 14)]
final class ShowProductController extends Controller
{
    /**
     * Detalhes de um produto
     *
     * Retorna os dados completos do produto informado.
     *
     * Requer a permission `products.view`.
     */
    public function __invoke(Product $product): ProductResource
    {
        Gate::authorize('view', $product);

        $product->load('category');

        return new ProductResource($product);
    }
}
