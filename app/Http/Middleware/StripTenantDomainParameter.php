<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class StripTenantDomainParameter
{
    /**
     * Remove o parâmetro de rota {tenant_domain} para que ele não seja
     * injetado nos argumentos dos controllers (preservando o Route Model Binding).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->route()?->forgetParameter('tenant_domain');

        return $next($request);
    }
}
