<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Auth;

use App\Actions\Central\Auth\LoginAction;
use App\DTOs\Central\Auth\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Auth\LoginRequest;
use App\Http\Resources\Central\Auth\LoginResource;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;

#[Group('Autenticação', 'Login e emissão do token JWT usado nas demais rotas.', weight: 0)]
final class LoginController extends Controller
{
    /**
     * Login
     *
     * Autentica um usuário **ativo** do banco central e devolve um token JWT.
     *
     * Envie o token nas demais rotas no header `Authorization: Bearer {access_token}`. Usuários desativados não conseguem logar.
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
