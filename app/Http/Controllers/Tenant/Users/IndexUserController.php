<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Users\UserResource;
use App\Models\Tenant\User;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Usuários', 'Usuários do tenant. As ações de edição, desativação e exclusão respeitam a hierarquia de roles do tenant.', weight: 11)]
final class IndexUserController extends Controller
{
    /**
     * Listar usuários do tenant
     *
     * Retorna lista paginada dos usuários pertencentes ao tenant atual.
     *
     * Requer a permission `users.view`.
     */
    #[QueryParameter('page', 'Número da página.', type: 'int', default: 1)]
    public function __invoke(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', User::class);

        return UserResource::collection(
            User::query()->orderBy('name')->paginate(),
        );
    }
}
