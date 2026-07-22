<?php

namespace App\Http\Middleware\GameAuth;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireGatewayServiceCredential
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedHash = config('game-auth.gateway.service_token_sha256');

        if (! is_string($expectedHash) || preg_match('/\A[a-f0-9]{64}\z/i', $expectedHash) !== 1) {
            return new JsonResponse(['error' => 'service_unavailable'], 503);
        }

        $credential = $request->bearerToken();

        if (! is_string($credential)
            || $credential === ''
            || ! hash_equals(strtolower($expectedHash), hash('sha256', $credential))
        ) {
            return new JsonResponse(['error' => 'unauthorized_service'], 401);
        }

        return $next($request);
    }
}
