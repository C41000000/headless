<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Users;

use App\Actions\Central\Users\CreateUserAction;
use App\DTOs\Central\Users\CreateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Users\CreateUserRequest;
use App\Http\Resources\Central\Users\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Usuários', 'Usuários do banco central (equipe que administra a plataforma). As ações de edição, desativação e exclusão respeitam a hierarquia de roles: super-admin só é gerenciado por super-admin; admin, por admin ou super-admin.', weight: 1)]
class StoreUserController extends Controller
{
    /**
     * Criar usuário
     *
     * Cria um usuário ativo no banco central, sem role.
     *
     * Requer a permission `users.create`.
     */
    public function __invoke(CreateUserRequest $request, CreateUserAction $action): UserResource
    {
        Gate::authorize('create', User::class);

        $user = $action->execute(CreateUserDTO::from($request->validated()));

        /** @status 201 */
        return new UserResource($user);

    }
}
