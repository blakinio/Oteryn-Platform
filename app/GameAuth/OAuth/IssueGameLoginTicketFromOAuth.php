<?php

namespace App\GameAuth\OAuth;

use App\GameAuth\Tickets\IssuedGameLoginTicket;
use App\GameAuth\Tickets\IssueGameLoginTicket;
use App\Identity\Models\Identity;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Client;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;
use LogicException;

final class IssueGameLoginTicketFromOAuth
{
    public function __construct(
        private readonly IssueGameLoginTicket $tickets,
        private readonly NativeOAuthClientManager $nativeClients,
    ) {}

    public function execute(Identity $identity, string $accessTokenId): IssuedGameLoginTicket
    {
        return DB::transaction(function () use ($identity, $accessTokenId): IssuedGameLoginTicket {
            $accessToken = Token::query()
                ->whereKey($accessTokenId)
                ->lockForUpdate()
                ->first();

            $tokenUserId = $accessToken?->getAttribute('user_id');
            $identityId = $identity->getAuthIdentifier();

            if (! $accessToken instanceof Token
                || (! is_int($tokenUserId) && ! is_string($tokenUserId))
                || (! is_int($identityId) && ! is_string($identityId))
                || $accessToken->revoked
                || $accessToken->expires_at === null
                || $accessToken->expires_at->lte(now())
                || (string) $tokenUserId !== (string) $identityId
                || ! $accessToken->can('game:ticket')
            ) {
                throw new OAuthBootstrapDenied;
            }

            $client = Client::query()
                ->whereKey($accessToken->client_id)
                ->lockForUpdate()
                ->first();

            if (! $client instanceof Client) {
                throw new OAuthBootstrapDenied;
            }

            try {
                $this->nativeClients->assertExpected($client);
            } catch (LogicException) {
                throw new OAuthBootstrapDenied;
            }

            $issued = $this->tickets->execute($identity);

            RefreshToken::query()
                ->where('access_token_id', $accessToken->getKey())
                ->where('revoked', false)
                ->update(['revoked' => true]);

            $accessToken->forceFill(['revoked' => true])->save();

            return $issued;
        });
    }
}
