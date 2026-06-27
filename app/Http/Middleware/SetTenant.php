<?php

namespace App\Http\Middleware;

use App\Support\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Define o tenant do painel a partir do usuário autenticado.
 * Aplicado às rotas web protegidas por auth.
 */
class SetTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            Tenant::set($user->account_id);
        }

        return $next($request);
    }
}
