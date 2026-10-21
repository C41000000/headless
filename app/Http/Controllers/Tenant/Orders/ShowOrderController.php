<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Orders;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Orders\OrderResource;
use App\Models\Tenant\Order;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Pedidos', 'Gestão de pedidos da loja do tenant.', weight: 15)]
final class ShowOrderController extends Controller
{
    /**
     * Visualizar pedido (Painel Administrativo)
     *
     * Retorna os detalhes de um pedido específico incluindo cliente, endereço e itens.
     *
     * Requer a permission `orders.view`.
     */
    public function __invoke(Order $order): OrderResource
    {
        Gate::authorize('view', $order);

        return new OrderResource($order->load(['items.product', 'customer']));
    }
}
