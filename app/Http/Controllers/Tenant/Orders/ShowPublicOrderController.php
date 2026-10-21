<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Orders;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Orders\OrderResource;
use App\Models\Tenant\Order;
use Dedoc\Scramble\Attributes\Group;

#[Group('Tenant - Loja Pública', 'Endpoints públicos da loja do tenant.', weight: 11)]
final class ShowPublicOrderController extends Controller
{
    /**
     * Consultar pedido
     *
     * Permite ao comprador visualizar o status e detalhes do pedido pelo UUID.
     */
    public function __invoke(Order $order): OrderResource
    {
        return new OrderResource($order->load(['items.product', 'customer']));
    }
}
