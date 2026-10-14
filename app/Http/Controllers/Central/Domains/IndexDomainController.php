<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Domains;

use App\Http\Controllers\Controller;
use App\Http\Resources\Central\Domains\DomainResource;
use App\Models\Central\Domain;
use App\Models\Central\Tenant;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

#[Group('Domínios', 'Domínios pelos quais um tenant é acessado. Todo tenant tem ao menos um.', weight: 3)]
final class IndexDomainController extends Controller
{
    /**
     * Listar domínios do tenant
     *
     * Lista completa (sem paginação), ordenada por domínio. Requer a permission `domains.view`.
     */
    public function __invoke(Tenant $tenant): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Domain::class);

        return DomainResource::collection($tenant->domains()->orderBy('domain')->get());
    }
}
