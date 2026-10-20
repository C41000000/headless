<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Tenants;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Tenants\IndexTenantRequest;
use App\Http\Resources\Central\Tenants\TenantResource;
use App\Models\Central\Tenant;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

#[Group('Tenants', 'Clientes da plataforma. Cada tenant tem banco próprio e é identificado pelos seus domínios.', weight: 2)]
final class IndexTenantController extends Controller
{
    /**
     * Listar tenants
     *
     * Lista paginada (15 por página), ordenada por nome, com os domínios de cada tenant. Tenants excluídos não aparecem.
     * Aceita `?search=` para filtrar por trecho do nome ou de um domínio.
     *
     * Requer a permission `tenants.view`.
     */
    #[QueryParameter('page', 'Número da página.', type: 'int', default: 1)]
    public function __invoke(IndexTenantRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Tenant::class);

        $search = trim((string) $request->validated('search'));

        return TenantResource::collection(
            Tenant::query()
                ->with('domains')
                ->when($search !== '', fn (Builder $query) => $query->where(
                    fn (Builder $query) => $query
                        ->whereLike('name', "%{$search}%")
                        ->orWhereHas('domains', fn (Builder $query) => $query->whereLike('domain', "%{$search}%")),
                ))
                ->orderBy('name')
                ->paginate()
                ->withQueryString(),
        );
    }
}
