<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Users;

use App\Actions\Central\Users\ToggleUserStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Central\Users\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Usuários', 'Usuários do banco central (equipe que administra a plataforma). As ações de edição, desativação e exclusão respeitam a hierarquia de roles: super-admin só é gerenciado por super-admin; admin, por admin ou super-admin.', weight: 1)]
final class ToggleUserStatusController extends Controller
{
    /**
     * Ativar ou desativar usuário
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
