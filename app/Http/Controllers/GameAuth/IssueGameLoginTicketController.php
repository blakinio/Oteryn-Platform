<?php

namespace App\Http\Controllers\GameAuth;

use App\GameAuth\OAuth\GameOAuthBootstrapDenied;
use App\GameAuth\OAuth\IssueGameLoginTicketFromOAuth;
use App\GameAuth\Tickets\GameLoginTicketDenied;
use App\Http\Requests\GameAuth\IssueGameLoginTicketRequest;
use App\Identity\Models\Identity;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Laravel\Passport\Contracts\ScopeAuthorizable;
use Symfony\Component\HttpFoundation\Response;

final class IssueGameLoginTicketController
{
    public function __invoke(
        IssueGameLoginTicketRequest $request,
        IssueGameLoginTicketFromOAuth $issuer,
    ): JsonResponse {
        $identity = $request->user('api');

        if (! $identity instanceof Identity) {
            return $this->error('unauthenticated', 'Authentication is required.', Response::HTTP_UNAUTHORIZED);
        }

        $token = $identity->token();

        if (! $token instanceof ScopeAuthorizable) {
            return $this->error('invalid_oauth_bootstrap', 'The sign-in bootstrap is invalid.', Response::HTTP_FORBIDDEN);
        }

        try {
            $issued = $issuer->execute($identity, $token);
        } catch (GameOAuthBootstrapDenied) {
            return $this->error('invalid_oauth_bootstrap', 'The sign-in bootstrap is invalid.', Response::HTTP_FORBIDDEN);
        } catch (GameLoginTicketDenied) {
            return $this->error('game_account_unavailable', 'Game access is not available for this account.', Response::HTTP_CONFLICT);
        } catch (QueryException) {
            return $this->error('temporarily_unavailable', 'Game authentication is temporarily unavailable.', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $expiresIn = max(1, $issued->expiresAt->getTimestamp() - now()->getTimestamp());
        $protocolVersion = config('game-auth.protocol_version');

        return response()->json([
            'protocol_version' => $protocolVersion,
            'ticket' => $issued->ticket,
            'expires_in' => $expiresIn,
        ], Response::HTTP_CREATED, $this->noStoreHeaders());
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status, $this->noStoreHeaders());
    }

    /**
     * @return array<string, string>
     */
    private function noStoreHeaders(): array
    {
        return [
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
        ];
    }
}
