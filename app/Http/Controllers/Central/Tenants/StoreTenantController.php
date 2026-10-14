<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Tenants;

use App\Actions\Central\Tenants\CreateTenantAction;
use App\DTOs\Central\Tenants\CreateTenantDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Tenants\CreateTenantRequest;
use App\Http\Resources\Central\Tenants\TenantResource;
use App\Models\Central\Tenant;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenants', 'Clientes da plataforma. Cada tenant tem banco próprio e é identificado pelos seus domínios.', weight: 2)]
final class StoreTenantController extends Controller
{
    /**
     * Criar tenant
     *
     * Cria o tenant com seus domínios e provisiona o banco dele (criação do banco e migrations) antes de responder. Se o provisionamento falhar, nada fica criado e os domínios continuam livres.
     *
     * Os domínios são normalizados para minúsculas e não podem ser domínios centrais da plataforma.
     *
     * Requer a permission `tenants.create`.
     */
    public function __invoke(CreateTenantRequest $request, CreateTenantAction $action): TenantResource
    {
        Gate::authorize('create', Tenant::class);

        $tenant = $action->execute(CreateTenantDTO::from($request->validated()));

        /** @status 201 */
        return new TenantResource($tenant);
    }
}
