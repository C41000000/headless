<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Tenants;

use App\Http\Controllers\Controller;
use App\Http\Resources\Central\Tenants\TenantResource;
use App\Models\Central\Tenant;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

#[Group('Tenants', 'Clientes da plataforma. Cada tenant tem banco próprio e é identificado pelos seus domínios.', weight: 2)]
final class IndexTenantController extends Controller
{
    /**
     * Listar tenants
     *
     * Lista paginada (15 por página), ordenada por nome, com os domínios de cada tenant. Tenants excluídos não aparecem.
     *
     * Requer a permission `tenants.view`.
     */
    #[QueryParameter('page', 'Número da página.', type: 'int', default: 1)]
    public function __invoke(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Tenant::class);

        return TenantResource::collection(
            Tenant::query()->with('domains')->orderBy('name')->paginate(),
        );
    }
}
