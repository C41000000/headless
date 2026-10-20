<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Users\IndexUserRequest;
use App\Http\Resources\Tenant\Users\UserResource;
use App\Models\Tenant\User;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Usuários', 'Usuários do tenant. As ações de edição, desativação e exclusão respeitam a hierarquia de roles do tenant.', weight: 11)]
final class IndexUserController extends Controller
{
    /**
     * Listar usuários do tenant
     *
     * Retorna lista paginada (15 por página), ordenada por nome, dos usuários do tenant atual, com o papel de cada um e o que o usuário autenticado pode fazer com ele (`abilities`).
     * Aceita `?search=` (nome ou e-mail), `?role=` e `?is_active=`.
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
