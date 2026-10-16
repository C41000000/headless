<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Users\UserResource;
use App\Models\Tenant\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Usuários', 'Usuários do tenant. As ações de edição, desativação e exclusão respeitam a hierarquia de roles do tenant.', weight: 11)]
final class ShowUserController extends Controller
{
    /**
     * Detalhar usuário do tenant
     *
     * Requer a permission `users.view`.
     */
    public function __invoke(User $user): UserResource
    {
        Gate::authorize('view', $user);

        return new UserResource($user);
    }
}
