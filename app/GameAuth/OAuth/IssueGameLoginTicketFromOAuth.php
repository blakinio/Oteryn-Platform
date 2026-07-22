<?php

namespace App\GameAuth\OAuth;

use App\GameAuth\Tickets\IssueGameLoginTicket;
use App\GameAuth\Tickets\IssuedGameLoginTicket;
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
            $client = $this->nativeClients->requireExisting(lockForUpdate: true);
            $scope = config('game-auth.oauth.scope');

            if (! $token instanceof Token
                || $token->revoked
                || $token->expires_at === null
                || $token->expires_at->lte(now())
                || ! is_string($scope)
                || $scope === ''
                || ! $token->can($scope)
                || (string) $token->client_id !== (string) $client->getKey()
                || (string) $token->user_id !== (string) $identity->getAuthIdentifier()
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
