<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Users;

use App\Actions\Tenant\Users\ToggleUserStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Users\UserResource;
use App\Models\Tenant\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Usuários', 'Usuários do tenant. As ações de edição, desativação e exclusão respeitam a hierarquia de roles do tenant.', weight: 11)]
final class ToggleUserStatusController extends Controller
{
    /**
     * Ativar ou desativar usuário do tenant
     *
     * Inverte o `is_active` do usuário. Usuário desativado não consegue logar.
     *
     * Requer a permission `users.toggle-status` e respeita a hierarquia. Ninguém pode alterar o próprio status.
     */
    public function __invoke(User $user, ToggleUserStatusAction $action): UserResource
    {
        Gate::authorize('toggleStatus', $user);

        $updatedUser = $action->execute($user);

        return new UserResource($updatedUser);
    }
}
