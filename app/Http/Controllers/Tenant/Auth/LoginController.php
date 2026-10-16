<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Auth;

use App\Actions\Tenant\Auth\LoginAction;
use App\DTOs\Tenant\Auth\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Auth\LoginRequest;
use App\Http\Resources\Tenant\Auth\LoginResource;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;

#[Group('Tenant - Autenticação', 'Login e emissão de token JWT para usuários do tenant.', weight: 10)]
final class LoginController extends Controller
{
    /**
     * Login do Tenant
     *
     * Autentica um usuário **ativo** no banco do tenant e devolve um token JWT.
     *
     * Envie o token nas rotas do tenant no header `Authorization: Bearer {access_token}`. Usuários desativados não conseguem logar.
     *
     * Limite de tentativas: 5 por minuto por e-mail + IP e 20 por minuto por IP. Acima disso a resposta é `429`.
     */
    #[Response(429, 'Muitas tentativas de login. Aguarde o tempo indicado no header `Retry-After`.')]
    public function __invoke(LoginRequest $request, LoginAction $loginAction): LoginResource
    {
        return new LoginResource(
            $loginAction->execute(LoginDTO::from($request->validated()))
        );
    }
}
