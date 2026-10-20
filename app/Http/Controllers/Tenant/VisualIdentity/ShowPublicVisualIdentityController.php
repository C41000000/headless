<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\VisualIdentity;

use App\Actions\Tenant\VisualIdentity\GetVisualIdentityAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\VisualIdentity\VisualIdentityResource;
use Dedoc\Scramble\Attributes\Group;

#[Group('Tenant - Identidade Visual', 'Identidade visual do tenant (cores, logo, favicon, tipografia e custom CSS).', weight: 12)]
final class ShowPublicVisualIdentityController extends Controller
{
    /**
     * Obter identidade visual pública do tenant
     *
     * Retorna as cores, URLs de logo, favicon e fontes configuradas para o tenant atual.
     * Endpoint público utilizado pelos frontends para estilização dinâmica.
     */
    public function __invoke(GetVisualIdentityAction $action): VisualIdentityResource
    {
        $visualIdentity = $action->execute();

        return new VisualIdentityResource($visualIdentity);
    }
}
