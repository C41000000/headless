<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Users;

use App\Actions\Central\Users\UpdateUserAction;
use App\DTOs\Central\Users\UpdateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Users\UpdateUserRequest;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Usuários', 'Usuários do banco central (equipe que administra a plataforma). As ações de edição, desativação e exclusão respeitam a hierarquia de roles: super-admin só é gerenciado por super-admin; admin, por admin ou super-admin.', weight: 1)]
class UpdateUserController extends Controller
{
    /**
     * Editar usuário
     *
     * Atualiza apenas os campos enviados.
     *
     * Requer a permission `users.update` e respeita a hierarquia: super-admin só é editado por super-admin; admin, por admin ou super-admin.
     */
    public function __invoke(User $user, UpdateUserRequest $request, UpdateUserAction $action): Response
    {
        Gate::authorize('update', $user);
        $action->execute($user, UpdateUserDTO::from($request->validated()));

        return response()->noContent();
    }
}
