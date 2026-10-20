<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\VisualIdentity;

use App\Actions\Tenant\VisualIdentity\GetVisualIdentityAction;
use App\Actions\Tenant\VisualIdentity\UpdateVisualIdentityAction;
use App\DTOs\Tenant\VisualIdentity\UpdateVisualIdentityDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\VisualIdentity\UpdateVisualIdentityRequest;
use App\Http\Resources\Tenant\VisualIdentity\VisualIdentityResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Identidade Visual', 'Identidade visual do tenant (cores, logo, favicon, tipografia e custom CSS).', weight: 12)]
final class UpdateVisualIdentityController extends Controller
{
    /**
     * Atualizar identidade visual do tenant
     *
     * Permite alterar cores, logotipo, logotipo escuro, favicon, fontes e CSS customizado.
     *
     * Requer a permission `visual-identity.update`.
     */
    public function __invoke(
        UpdateVisualIdentityRequest $request,
        GetVisualIdentityAction $getAction,
        UpdateVisualIdentityAction $updateAction,
    ): VisualIdentityResource {
        $visualIdentity = $getAction->execute();

        Gate::authorize('update', $visualIdentity);

        $updated = $updateAction->execute(UpdateVisualIdentityDTO::from($request->validated()));

        return new VisualIdentityResource($updated);
    }
}
