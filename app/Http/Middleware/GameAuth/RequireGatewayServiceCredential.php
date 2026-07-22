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
        $expectedHashes = $this->configuredHashes(config('game-auth.gateway.service_token_sha256s'));

        if ($expectedHashes === null) {
            return new JsonResponse(['error' => 'service_unavailable'], 503);
        }

        $credential = $request->bearerToken();
        $credentialIsBounded = is_string($credential)
            && strlen($credential) >= 32
            && strlen($credential) <= 1024;
        $presentedHash = hash('sha256', $credentialIsBounded ? $credential : '');
        $authorized = false;

        foreach ($expectedHashes as $expectedHash) {
            $authorized = hash_equals($expectedHash, $presentedHash) || $authorized;
        }

        if (! $credentialIsBounded || ! $authorized) {
            return new JsonResponse(['error' => 'unauthorized_service'], 401);
        }

        return $next($request);
    }

    /**
     * @return list<string>|null
     */
    private function configuredHashes(mixed $configuredHashes): ?array
    {
        if (! is_array($configuredHashes) || $configuredHashes === []) {
            return null;
        }

        $hashes = [];

        foreach ($configuredHashes as $configuredHash) {
            if (! is_string($configuredHash) || preg_match('/\A[a-f0-9]{64}\z/', $configuredHash) !== 1) {
                return null;
            }

            $hashes[] = $configuredHash;
        }

        return $hashes;
    }
}
