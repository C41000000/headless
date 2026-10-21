<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Products;

use App\Actions\Tenant\Products\DeleteProductAction;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Catálogo', 'Categorias e produtos da loja do tenant.', weight: 14)]
final class DeleteProductController extends Controller
{
    /**
     * Excluir produto
     *
     * Realiza a exclusão lógica (soft delete) do produto.
     *
     * Requer a permission `products.delete`.
     */
    public function __invoke(Product $product, DeleteProductAction $action): Response
    {
        Gate::authorize('delete', $product);

        $action->execute($product);

        return response()->noContent();
    }
}
