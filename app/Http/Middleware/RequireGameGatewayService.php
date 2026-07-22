<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireGameGatewayService
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredHashes = config('game-auth.gateway.service_token_hashes');

        if (! $this->validConfiguration($configuredHashes)) {
            return $this->error(
                code: 'temporarily_unavailable',
                message: 'Game authentication service is unavailable.',
                status: Response::HTTP_SERVICE_UNAVAILABLE,
            );
        }

        $credential = $request->bearerToken();
        $credentialIsBounded = is_string($credential)
            && strlen($credential) >= 32
            && strlen($credential) <= 1024;
        $presentedHash = hash('sha256', $credentialIsBounded ? $credential : '');
        $authorized = false;

        foreach ($configuredHashes as $configuredHash) {
            $authorized = hash_equals($configuredHash, $presentedHash) || $authorized;
        }

        if (! $credentialIsBounded || ! $authorized) {
            return $this->error(
                code: 'unauthorized_service',
                message: 'Service authentication failed.',
                status: Response::HTTP_UNAUTHORIZED,
                headers: ['WWW-Authenticate' => 'Bearer'],
            );
        }

        return $next($request);
    }

    /**
     * @param  mixed  $configuredHashes
     */
    private function validConfiguration(mixed $configuredHashes): bool
    {
        if (! is_array($configuredHashes) || $configuredHashes === []) {
            return false;
        }

        foreach ($configuredHashes as $configuredHash) {
            if (! is_string($configuredHash) || preg_match('/^[a-f0-9]{64}$/', $configuredHash) !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function error(string $code, string $message, int $status, array $headers = []): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status, $headers);
    }
}
