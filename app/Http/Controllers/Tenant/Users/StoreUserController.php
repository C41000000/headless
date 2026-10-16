<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Users;

use App\Actions\Tenant\Users\CreateUserAction;
use App\DTOs\Tenant\Users\CreateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Users\CreateUserRequest;
use App\Http\Resources\Tenant\Users\UserResource;
use App\Models\Tenant\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Usuários', 'Usuários do tenant. As ações de edição, desativação e exclusão respeitam a hierarquia de roles do tenant.', weight: 11)]
final class StoreUserController extends Controller
{
    /**
     * Criar usuário no tenant
     *
     * Cria um novo usuário ativo no banco do tenant atual.
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
