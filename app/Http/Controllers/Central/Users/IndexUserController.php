<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Users\IndexUserRequest;
use App\Http\Resources\Central\Users\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

#[Group('Usuários', 'Usuários do banco central (equipe que administra a plataforma). As ações de edição, desativação e exclusão respeitam a hierarquia de roles: super-admin só é gerenciado por super-admin; admin, por admin ou super-admin.', weight: 1)]
final class IndexUserController extends Controller
{
    /**
     * Listar usuários
     *
     * Lista paginada (15 por página), ordenada por nome, com o papel de cada usuário e o que o usuário autenticado pode fazer com ele (`abilities`).
     * Aceita `?search=` (nome ou e-mail), `?role=` e `?is_active=`. Usuários excluídos não aparecem.
     *
     * Requer a permission `users.view`.
     */
    #[QueryParameter('page', 'Número da página.', type: 'int', default: 1)]
    public function __invoke(IndexUserRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', User::class);

        $search = trim((string) $request->validated('search'));
        $role = $request->validated('role');

        return UserResource::collection(
            User::query()
                ->with('roles')
                ->when($search !== '', fn (Builder $query) => $query->where(
                    fn (Builder $query) => $query
                        ->whereLike('name', "%{$search}%")
                        ->orWhereLike('email', "%{$search}%"),
                ))
                ->when(filled($role), fn (Builder $query) => $query->whereHas(
                    'roles',
                    fn (Builder $query) => $query->where('name', $role),
                ))
                ->when(
                    $request->validated('is_active') !== null,
                    fn (Builder $query) => $query->where('is_active', $request->boolean('is_active')),
                )
                ->orderBy('name')
                ->paginate()
                ->withQueryString(),
        );
    }
}
