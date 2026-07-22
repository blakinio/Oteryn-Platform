<?php

namespace App\Http\Controllers\GameAuth;

use App\GameAuth\OAuth\IssueGameLoginTicketFromOAuth;
use App\GameAuth\OAuth\OAuthBootstrapDenied;
use App\GameAuth\Tickets\GameLoginTicketDenied;
use App\Identity\Models\Identity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Token;

final class GameLoginTicketIssueController
{
    public function __invoke(Request $request, IssueGameLoginTicketFromOAuth $issuer): JsonResponse
    {
        $request->validate([
            'protocol_version' => ['required', 'integer', 'in:1'],
            'identity_id' => ['prohibited'],
            'account_id' => ['prohibited'],
            'canary_account_id' => ['prohibited'],
        ]);

        $identity = $request->user('api');

        if (! $identity instanceof Identity) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        $accessToken = $identity->currentAccessToken();
        $accessTokenId = $accessToken?->getKey();

        if (! $accessToken instanceof Token || (! is_int($accessTokenId) && ! is_string($accessTokenId))) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        try {
            $issued = $issuer->execute($identity, (string) $accessTokenId);
        } catch (OAuthBootstrapDenied|GameLoginTicketDenied) {
            return response()->json(['error' => 'game_login_unavailable'], 401);
        }

        return response()->json([
            'protocol_version' => 1,
            'ticket' => $issued->ticket,
            'expires_in' => max(0, now()->diffInSeconds($issued->expiresAt, false)),
        ]);
    }
}
