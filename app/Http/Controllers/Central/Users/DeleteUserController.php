<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Users;

use App\Actions\Central\Users\DeleteUserAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Usuários', 'Usuários do banco central (equipe que administra a plataforma). As ações de edição, desativação e exclusão respeitam a hierarquia de roles: super-admin só é gerenciado por super-admin; admin, por admin ou super-admin.', weight: 1)]
final class DeleteUserController extends Controller
{
    /**
     * Excluir usuário
     *
     * Exclusão lógica (soft delete): o usuário deixa de aparecer e não consegue mais logar.
     *
     * Requer a permission `users.delete` (hoje só o super-admin tem) e respeita a hierarquia. Ninguém pode excluir a própria conta.
     */
    public function __invoke(User $user, DeleteUserAction $action): Response
    {
        Gate::authorize('delete', $user);

        $action->execute($user);

        return response()->noContent();
    }
}
