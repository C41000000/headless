<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Orders;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Orders\OrderResource;
use App\Models\Tenant\Order;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Pedidos', 'Gestão de pedidos da loja do tenant.', weight: 15)]
final class IndexOrderController extends Controller
{
    /**
     * Listar todos os pedidos (Painel Administrativo)
     *
     * Retorna a listagem paginada de pedidos com filtros por status e busca por cliente ou número do pedido.
     *
     * Requer a permission `orders.view`.
     */
    #[QueryParameter('page', 'Número da página.', type: 'int', default: 1)]
    #[QueryParameter('search', 'Buscar por número do pedido, nome ou e-mail do cliente.', type: 'string')]
    #[QueryParameter('status', 'Filtrar por status do pedido (pending, paid, preparing, shipped, delivered, cancelled).', type: 'string')]
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Order::class);

        $query = Order::query()->with(['items.product', 'customer'])->withCount('items');

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->where(function (Builder $q) use ($search) {
                $q->whereLike('order_number', "%{$search}%")
                    ->orWhereLike('customer_name', "%{$search}%")
                    ->orWhereLike('customer_email', "%{$search}%")
                    ->orWhereLike('customer_document', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        return OrderResource::collection(
            $query->latest()->paginate((int) $request->query('per_page', 15))
        );
    }
}
