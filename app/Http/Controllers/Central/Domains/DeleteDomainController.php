<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Domains;

use App\Actions\Central\Domains\DeleteDomainAction;
use App\Http\Controllers\Controller;
use App\Models\Central\Domain;
use App\Models\Central\Tenant;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Domínios', 'Domínios pelos quais um tenant é acessado. Todo tenant tem ao menos um.', weight: 3)]
final class DeleteDomainController extends Controller
{
    /**
     * Remover domínio
     *
     * Remove o domínio do tenant. O último domínio não pode ser removido (resposta `422`), para o tenant nunca ficar inacessível.
     *
     * Requer a permission `domains.delete` (hoje só o super-admin tem).
     *
     * O domínio precisa pertencer ao tenant da URL; caso contrário, a resposta é `404`.
     */
    public function __invoke(Tenant $tenant, Domain $domain, DeleteDomainAction $action): Response
    {
        Gate::authorize('delete', $domain);

        $action->execute($domain);

        return response()->noContent();
    }
}
