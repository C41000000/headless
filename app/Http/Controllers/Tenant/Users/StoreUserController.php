<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Users;

use App\Actions\Tenant\Users\CreateUserAction;
use App\DTOs\Tenant\Users\CreateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Users\CreateUserRequest;
use App\Http\Resources\Tenant\Users\UserResource;
use App\Models\Tenant\User;
use App\Support\Enums\Tenant\RoleName;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Usuários', 'Usuários do tenant. As ações de edição, desativação e exclusão respeitam a hierarquia de roles do tenant.', weight: 11)]
final class StoreUserController extends Controller
{
    /**
     * Criar usuário no tenant
     *
     * Cria um novo usuário ativo no banco do tenant atual, com o papel informado (de nível igual ou menor que o seu).
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
