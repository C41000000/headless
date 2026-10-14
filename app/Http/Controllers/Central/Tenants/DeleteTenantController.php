<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Tenants;

use App\Actions\Central\Tenants\DeleteTenantAction;
use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Tenants', 'Clientes da plataforma. Cada tenant tem banco próprio e é identificado pelos seus domínios.', weight: 2)]
final class DeleteTenantController extends Controller
{
    /**
     * Excluir tenant
     *
     * Exclusão lógica (soft delete): o tenant some da API e os domínios dele param de responder, mas o **banco do tenant é mantido**, permitindo restaurar. Os domínios continuam reservados e não podem ser usados por outro tenant.
     *
     * Requer a permission `tenants.delete` (hoje só o super-admin tem).
     */
    public function __invoke(Tenant $tenant, DeleteTenantAction $action): Response
    {
        Gate::authorize('delete', $tenant);

        $action->execute($tenant);

        return response()->noContent();
    }
}
