<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Central\Users\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Usuários', 'Usuários do banco central (equipe que administra a plataforma). As ações de edição, desativação e exclusão respeitam a hierarquia de roles: super-admin só é gerenciado por super-admin; admin, por admin ou super-admin.', weight: 1)]
class ShowUserController extends Controller
{
    /**
     * Detalhar usuário
     *
     * Requer a permission `users.view`.
     */
    public function __invoke(User $user): UserResource
    {
        Gate::authorize('view', $user);

        return new UserResource($user);
    }
}
