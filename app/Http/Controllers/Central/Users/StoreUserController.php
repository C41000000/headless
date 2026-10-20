<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Users;

use App\Actions\Central\Users\CreateUserAction;
use App\DTOs\Central\Users\CreateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Users\CreateUserRequest;
use App\Http\Resources\Central\Users\UserResource;
use App\Models\User;
use App\Support\Enums\RoleName;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Usuários', 'Usuários do banco central (equipe que administra a plataforma). As ações de edição, desativação e exclusão respeitam a hierarquia de roles: super-admin só é gerenciado por super-admin; admin, por admin ou super-admin.', weight: 1)]
class StoreUserController extends Controller
{
    /**
     * Criar usuário
     *
     * Cria um usuário ativo no banco central com o papel informado (de nível igual ou menor que o seu).
     *
     * Requer a permission `users.create`. Atribuir um papel acima do seu retorna 403.
     */
    public function __invoke(CreateUserRequest $request, CreateUserAction $action): UserResource
    {
        Gate::authorize('create', [User::class, $request->enum('role', RoleName::class)]);

        $user = $action->execute(CreateUserDTO::from($request->validated()));

        /** @status 201 */
        return new UserResource($user);

    }
}
