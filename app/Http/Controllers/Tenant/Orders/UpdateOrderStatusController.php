<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Orders;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Orders\OrderResource;
use App\Models\Tenant\Order;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

#[Group('Tenant - Pedidos', 'Gestão de pedidos da loja do tenant.', weight: 15)]
final class UpdateOrderStatusController extends Controller
{
    /**
     * Atualizar status do pedido
     *
     * Permite alterar o status do pedido (pending, paid, preparing, shipped, delivered, cancelled).
     * Se cancelado, o estoque dos itens é devolvido automaticamente.
     *
     * Requer a permission `orders.update`.
     */
    public function __invoke(Request $request, Order $order): OrderResource
    {
        Gate::authorize('update', $order);

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in([
                    Order::STATUS_PENDING,
                    Order::STATUS_PAID,
                    Order::STATUS_PREPARING,
                    Order::STATUS_SHIPPED,
                    Order::STATUS_DELIVERED,
                    Order::STATUS_CANCELLED,
                ]),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'status.required' => 'O status do pedido é obrigatório.',
            'status.in' => 'Status do pedido inválido.',
        ]);

        DB::transaction(function () use ($order, $validated) {
            $oldStatus = $order->status;
            $newStatus = $validated['status'];

            // Se o pedido for cancelado agora, devolve o estoque
            if ($newStatus === Order::STATUS_CANCELLED && $oldStatus !== Order::STATUS_CANCELLED) {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            }

            $order->update([
                'status' => $newStatus,
                'notes' => $validated['notes'] ?? $order->notes,
            ]);
        });

        return new OrderResource($order->fresh(['items.product', 'customer']));
    }
}
