<?php

namespace App\Http\Middleware;

use App\Models\Account;
use App\Support\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve o widget_token da URL pública para uma conta e fixa o tenant.
 * Usado pela API do widget (sem autenticação de usuário).
 */
class ResolveWidgetToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token');

        $account = Account::where('widget_token', $token)->first();
        abort_unless($account, 404);

        Tenant::set($account->id);
        $request->attributes->set('account', $account);

        return $next($request);
    }
}
