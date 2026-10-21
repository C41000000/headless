<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Products;

use App\Actions\Tenant\Products\CreateProductAction;
use App\DTOs\Tenant\Products\CreateProductDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Products\StoreProductRequest;
use App\Http\Resources\Tenant\Products\ProductResource;
use App\Models\Tenant\Product;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Catálogo', 'Categorias e produtos da loja do tenant.', weight: 14)]
final class StoreProductController extends Controller
{
    /**
     * Criar novo produto
     *
     * Cadastra um novo produto no catálogo do tenant.
     *
     * Requer a permission `products.create`.
     */
    public function __invoke(StoreProductRequest $request, CreateProductAction $action): ProductResource
    {
        Gate::authorize('create', Product::class);

        $product = $action->execute(CreateProductDTO::from($request->validated()));

        /** @status 201 */
        return new ProductResource($product);
    }
}
