<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe a rota aos domínios centrais (config tenancy.central_domains).
 *
 * Usado no lugar de Route::domain() por domínio: com mais de um domínio central
 * os nomes das rotas se repetem e o route:cache quebra.
 */
final class EnsureCentralDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(in_array($request->getHost(), config('tenancy.central_domains'), true), Response::HTTP_NOT_FOUND);

        return $next($request);
    }
}
