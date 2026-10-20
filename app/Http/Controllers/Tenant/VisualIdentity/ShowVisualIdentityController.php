<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\VisualIdentity;

use App\Actions\Tenant\VisualIdentity\GetVisualIdentityAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\VisualIdentity\VisualIdentityResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Identidade Visual', 'Identidade visual do tenant (cores, logo, favicon, tipografia e custom CSS).', weight: 12)]
final class ShowVisualIdentityController extends Controller
{
    /**
     * Visualizar identidade visual (Painel Administrativo)
     *
     * Retorna os detalhes de identidade visual do tenant no contexto autenticado.
     *
     * Requer a permission `visual-identity.view`.
     */
    public function __invoke(GetVisualIdentityAction $action): VisualIdentityResource
    {
        $visualIdentity = $action->execute();

        Gate::authorize('view', $visualIdentity);

        return new VisualIdentityResource($visualIdentity);
    }
}
