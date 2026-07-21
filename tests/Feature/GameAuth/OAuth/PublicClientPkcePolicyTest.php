<?php

namespace Tests\Feature\GameAuth\OAuth;

use App\GameAuth\OAuth\NativeOAuthClientManager;
use App\Identity\Models\Identity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Tests\TestCase;

final class PublicClientPkcePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_client_authorization_requires_s256_method(): void
    {
        $identity = Identity::query()->create([
            'email' => 'person@example.com',
            'password' => Hash::make('Correct-Horse-9!Battery'),
        ]);
        $client = $this->app->make(NativeOAuthClientManager::class)->ensure();
        $challenge = rtrim(strtr(base64_encode(hash('sha256', 'verifier', true)), '+/', '-_'), '=');

        $this->actingAs($identity, 'web')->get($this->authorizationUrl($client, $challenge, 'plain'))
            ->assertStatus(400);

        $this->actingAs($identity, 'web')->get($this->authorizationUrl($client, $challenge, null))
            ->assertStatus(400);

        $this->actingAs($identity, 'web')->get($this->authorizationUrl($client, $challenge, 'S256'))
            ->assertOk();
    }

    private function authorizationUrl(Client $client, string $challenge, ?string $method): string
    {
        $query = [
            'client_id' => $client->getKey(),
            'redirect_uri' => 'http://127.0.0.1:49158/callback',
            'response_type' => 'code',
            'scope' => 'game:ticket',
            'state' => 'pkce-policy-state',
            'code_challenge' => $challenge,
        ];

        if ($method !== null) {
            $query['code_challenge_method'] = $method;
        }

        return '/oauth/authorize?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
}
