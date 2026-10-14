<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Tenants;

use App\Http\Controllers\Controller;
use App\Http\Resources\Central\Tenants\TenantResource;
use App\Models\Central\Tenant;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenants', 'Clientes da plataforma. Cada tenant tem banco próprio e é identificado pelos seus domínios.', weight: 2)]
final class ShowTenantController extends Controller
{
    /**
     * Detalhar tenant
     *
     * Inclui os domínios do tenant. Requer a permission `tenants.view`.
     */
    public function __invoke(Tenant $tenant): TenantResource
    {
        Gate::authorize('view', $tenant);

        return new TenantResource($tenant->load('domains'));
    }
}
