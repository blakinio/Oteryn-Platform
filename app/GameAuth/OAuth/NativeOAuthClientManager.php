<?php

namespace App\GameAuth\OAuth;

use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use LogicException;

final class NativeOAuthClientManager
{
    public function __construct(
        private readonly ClientRepository $clients,
    ) {}

    public function ensure(): Client
    {
        $name = $this->configString('game-auth.oauth.native_client_name');
        $redirectUri = $this->nativeRedirectUri();

        $matches = Client::query()
            ->where('name', $name)
            ->where('revoked', false)
            ->get();

        if ($matches->count() > 1) {
            throw new LogicException('Multiple active OAuth clients use the configured Oteryn native client name.');
        }

        $existing = $matches->first();

        if ($existing instanceof Client) {
            $this->assertExpectedClient($existing, $redirectUri);

            return $existing;
        }

        return $this->clients->createAuthorizationCodeGrantClient(
            name: $name,
            redirectUris: [$redirectUri],
            confidential: false,
        );
    }

    private function assertExpectedClient(Client $client, string $redirectUri): void
    {
        $redirectUris = $client->getAttribute('redirect_uris');

        if (! is_array($redirectUris)
            || $client->confidential()
            || $client->getAttribute('owner_id') !== null
            || $client->getAttribute('owner_type') !== null
            || ! $client->hasGrantType('authorization_code')
            || $redirectUris !== [$redirectUri]
        ) {
            throw new LogicException('Existing Oteryn native OAuth client does not match the required public PKCE contract.');
        }
    }

    private function nativeRedirectUri(): string
    {
        $redirectUri = $this->configString('game-auth.oauth.native_redirect_uri');
        $parts = parse_url($redirectUri);

        if (! is_array($parts)
            || ($parts['scheme'] ?? null) !== 'http'
            || ($parts['host'] ?? null) !== '127.0.0.1'
            || ($parts['path'] ?? null) !== '/callback'
            || isset($parts['port'])
            || isset($parts['query'])
            || isset($parts['fragment'])
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            throw new LogicException('Native OAuth redirect URI must be exactly http://127.0.0.1/callback.');
        }

        return $redirectUri;
    }

    private function configString(string $key): string
    {
        $value = config($key);

        if (! is_string($value) || trim($value) === '') {
            throw new LogicException("Invalid string configuration: {$key}.");
        }

        return $value;
    }
}
