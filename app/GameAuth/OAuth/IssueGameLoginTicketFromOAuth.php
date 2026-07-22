<?php

namespace App\GameAuth\OAuth;

use App\GameAuth\Tickets\IssuedGameLoginTicket;
use App\GameAuth\Tickets\IssueGameLoginTicket;
use App\Identity\Models\Identity;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Contracts\ScopeAuthorizable;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

final class IssueGameLoginTicketFromOAuth
{
    public function __construct(
        private readonly NativeOAuthClientManager $nativeClients,
        private readonly IssueGameLoginTicket $tickets,
    ) {}

    public function execute(Identity $identity, ScopeAuthorizable $presentedToken): IssuedGameLoginTicket
    {
        $tokenId = $this->tokenId($presentedToken);

        return DB::transaction(function () use ($identity, $tokenId): IssuedGameLoginTicket {
            $token = Token::query()
                ->lockForUpdate()
                ->find($tokenId);
            $client = $this->nativeClients->requireExisting();
            $scope = config('game-auth.oauth.scope');
            $tokenClientId = $token?->getAttribute('client_id');
            $tokenUserId = $token?->getAttribute('user_id');
            $clientId = $client->getKey();
            $identityId = $identity->getAuthIdentifier();

            if (! $token instanceof Token
                || $token->revoked
                || $token->expires_at === null
                || $token->expires_at->lte(now())
                || ! is_string($scope)
                || $scope === ''
                || ! $token->can($scope)
                || ! is_string($tokenClientId)
                || ! is_string($clientId)
                || ! hash_equals($clientId, $tokenClientId)
                || (! is_int($tokenUserId) && ! is_string($tokenUserId))
                || (! is_int($identityId) && ! is_string($identityId))
                || (string) $tokenUserId !== (string) $identityId
            ) {
                throw new GameOAuthBootstrapDenied;
            }

            $issued = $this->tickets->execute($identity);

            RefreshToken::query()
                ->where('access_token_id', $token->getKey())
                ->lockForUpdate()
                ->get()
                ->each(static function (RefreshToken $refreshToken): void {
                    $refreshToken->revoke();
                });

            $token->revoke();

            return $issued;
        });
    }

    private function tokenId(ScopeAuthorizable $presentedToken): string
    {
        if ($presentedToken instanceof Token) {
            $tokenId = $presentedToken->getKey();
        } elseif ($presentedToken instanceof AccessToken) {
            $tokenId = $presentedToken->toArray()['oauth_access_token_id'] ?? null;
        } else {
            $tokenId = null;
        }

        if (! is_string($tokenId) || $tokenId === '') {
            throw new GameOAuthBootstrapDenied;
        }

        return $tokenId;
    }
}
