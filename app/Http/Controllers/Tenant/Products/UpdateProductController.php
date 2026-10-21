<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Products;

use App\Actions\Tenant\Products\UpdateProductAction;
use App\DTOs\Tenant\Products\UpdateProductDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Products\UpdateProductRequest;
use App\Http\Resources\Tenant\Products\ProductResource;
use App\Models\Tenant\Product;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Catálogo', 'Categorias e produtos da loja do tenant.', weight: 14)]
final class UpdateProductController extends Controller
{
    /**
     * Atualizar produto
     *
     * Atualiza os dados de um produto (nome, preço, estoque, categoria, status, etc).
     *
     * Requer a permission `products.update`.
     */
    public function __invoke(UpdateProductRequest $request, Product $product, UpdateProductAction $action): ProductResource
    {
        Gate::authorize('update', $product);

        $updated = $action->execute($product, UpdateProductDTO::from($request->validated()));

        return new ProductResource($updated);
    }
}
