<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Domains;

use App\Actions\Central\Domains\CreateDomainAction;
use App\DTOs\Central\Domains\DomainDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Domains\DomainRequest;
use App\Http\Resources\Central\Domains\DomainResource;
use App\Models\Central\Domain;
use App\Models\Central\Tenant;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Domínios', 'Domínios pelos quais um tenant é acessado. Todo tenant tem ao menos um.', weight: 3)]
final class StoreDomainController extends Controller
{
    /**
     * Adicionar domínio
     *
     * Adiciona um domínio ao tenant. O domínio é normalizado para minúsculas, precisa ser único na plataforma e não pode ser um domínio central.
     *
     * Requer a permission `domains.create`.
     */
    public function __invoke(Tenant $tenant, DomainRequest $request, CreateDomainAction $action): DomainResource
    {
        Gate::authorize('create', Domain::class);

        /** @status 201 */
        return new DomainResource($action->execute($tenant, DomainDTO::from($request->validated())));
    }
}
