<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Orders;

use App\Actions\Tenant\Orders\CreateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Orders\StorePublicOrderRequest;
use App\Http\Resources\Tenant\Orders\OrderResource;
use Dedoc\Scramble\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('Tenant - Loja Pública', 'Endpoints públicos da loja do tenant.', weight: 11)]
final class StorePublicOrderController extends Controller
{
    /**
     * Criar novo pedido
     *
     * Cria um novo pedido na loja pública com validação de estoque e preços.
     */
    public function __invoke(StorePublicOrderRequest $request, CreateOrderAction $action): Response
    {
        $order = $action->execute($request->validated());

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
