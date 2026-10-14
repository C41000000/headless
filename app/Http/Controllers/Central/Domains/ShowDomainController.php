<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Domains;

use App\Http\Controllers\Controller;
use App\Http\Resources\Central\Domains\DomainResource;
use App\Models\Central\Domain;
use App\Models\Central\Tenant;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Domínios', 'Domínios pelos quais um tenant é acessado. Todo tenant tem ao menos um.', weight: 3)]
final class ShowDomainController extends Controller
{
    /**
     * Detalhar domínio
     *
     * Requer a permission `domains.view`.
     *
     * O domínio precisa pertencer ao tenant da URL; caso contrário, a resposta é `404`.
     */
    public function __invoke(Tenant $tenant, Domain $domain): DomainResource
    {
        Gate::authorize('view', $domain);

        return new DomainResource($domain);
    }
}
