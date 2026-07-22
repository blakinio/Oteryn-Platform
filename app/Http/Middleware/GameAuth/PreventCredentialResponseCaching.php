<?php

namespace App\Http\Middleware\GameAuth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PreventCredentialResponseCaching
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return self::apply($request, $next($request));
    }

    public static function apply(Request $request, Response $response): Response
    {
        if ($request->is('api/v1/game-auth/tickets')
            || $request->is('internal/v1/game-auth/tickets/redeem')
            || $request->is('internal/v1/game-auth/login-context')
        ) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
