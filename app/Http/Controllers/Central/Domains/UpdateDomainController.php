<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Domains;

use App\Actions\Central\Domains\UpdateDomainAction;
use App\DTOs\Central\Domains\DomainDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Domains\DomainRequest;
use App\Models\Central\Domain;
use App\Models\Central\Tenant;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Domínios', 'Domínios pelos quais um tenant é acessado. Todo tenant tem ao menos um.', weight: 3)]
final class UpdateDomainController extends Controller
{
    /**
     * Editar domínio
     *
     * Troca o endereço do domínio. Mesmas regras da criação; enviar o próprio domínio atual é permitido.
     *
     * Requer a permission `domains.update`.
     *
     * O domínio precisa pertencer ao tenant da URL; caso contrário, a resposta é `404`.
     */
    public function __invoke(Tenant $tenant, Domain $domain, DomainRequest $request, UpdateDomainAction $action): Response
    {
        Gate::authorize('update', $domain);

        $action->execute($domain, DomainDTO::from($request->validated()));

        return response()->noContent();
    }
}
