<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Auth\AuthUserResource;
use App\Models\Tenant\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Tenant - Autenticação', 'Login e emissão de token JWT para usuários do tenant.', weight: 10)]
final class ShowAuthenticatedUserController extends Controller
{
    /**
     * Usuário autenticado
     *
     * Retorna o usuário do tenant dono do token, com o papel e as permissions efetivas.
     */
    public function __invoke(Request $request): AuthUserResource
    {
        /** @var User $user */
        $user = $request->user();

        return new AuthUserResource($user);
    }
}
