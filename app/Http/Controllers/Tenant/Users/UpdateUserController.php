<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Users;

use App\Actions\Tenant\Users\UpdateUserAction;
use App\DTOs\Tenant\Users\UpdateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Users\UpdateUserRequest;
use App\Models\Tenant\User;
use App\Support\Enums\Tenant\RoleName;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Usuários', 'Usuários do tenant. As ações de edição, desativação e exclusão respeitam a hierarquia de roles do tenant.', weight: 11)]
final class UpdateUserController extends Controller
{
    /**
     * Editar usuário do tenant
     *
     * Atualiza apenas os campos enviados.
     *
     * Requer a permission `users.update` e respeita a hierarquia do tenant. Também não é possível atribuir um papel acima do seu.
     */
    public function __invoke(User $user, UpdateUserRequest $request, UpdateUserAction $action): Response
    {
        Gate::authorize('update', [$user, $request->enum('role', RoleName::class)]);

        $action->execute($user, UpdateUserDTO::from($request->validated()));

        return response()->noContent();
    }
}
