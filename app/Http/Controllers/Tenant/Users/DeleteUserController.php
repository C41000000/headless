<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Users;

use App\Actions\Tenant\Users\DeleteUserAction;
use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Usuários', 'Usuários do tenant. As ações de edição, desativação e exclusão respeitam a hierarquia de roles do tenant.', weight: 11)]
final class DeleteUserController extends Controller
{
    /**
     * Excluir usuário do tenant
     *
     * Exclusão lógica (soft delete): o usuário deixa de aparecer e não consegue mais logar.
     *
     * Requer a permission `users.delete` e respeita a hierarquia. Ninguém pode excluir a própria conta.
     */
    public function __invoke(User $user, DeleteUserAction $action): Response
    {
        Gate::authorize('delete', $user);

        $action->execute($user);

        return response()->noContent();
    }
}
