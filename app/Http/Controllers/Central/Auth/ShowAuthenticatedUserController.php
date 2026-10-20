<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Central\Auth\AuthUserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Autenticação', 'Login e emissão do token JWT usado nas demais rotas.', weight: 0)]
final class ShowAuthenticatedUserController extends Controller
{
    /**
     * Usuário autenticado
     *
     * Retorna o usuário dono do token, com o papel e as permissions efetivas.
     */
    public function __invoke(Request $request): AuthUserResource
    {
        /** @var User $user */
        $user = $request->user();

        return new AuthUserResource($user);
    }
}
