<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Tenants;

use App\Actions\Central\Tenants\UpdateTenantAction;
use App\DTOs\Central\Tenants\UpdateTenantDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Tenants\UpdateTenantRequest;
use App\Models\Central\Tenant;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Tenants', 'Clientes da plataforma. Cada tenant tem banco próprio e é identificado pelos seus domínios.', weight: 2)]
final class UpdateTenantController extends Controller
{
    /**
     * Editar tenant
     *
     * Atualiza apenas os campos enviados. Domínios são gerenciados pelas rotas de domínio.
     *
     * Requer a permission `tenants.update`.
     */
    public function __invoke(Tenant $tenant, UpdateTenantRequest $request, UpdateTenantAction $action): Response
    {
        Gate::authorize('update', $tenant);

        $action->execute($tenant, UpdateTenantDTO::from($request->validated()));

        return response()->noContent();
    }
}
