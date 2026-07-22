<?php

namespace Tests\Feature\GameAuth\OAuth;

use App\GameAuth\OAuth\NativeOAuthClientManager;
use App\Identity\Models\Identity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\GameAuth\OAuth\Concerns\ConfiguresEphemeralPassportKeys;
use Tests\TestCase;

final class NativeOAuthGrantPolicyTest extends TestCase
{
    use ConfiguresEphemeralPassportKeys;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureEphemeralPassportKeys();
    }

    public function test_native_client_cannot_use_password_grant(): void
    {
        $identity = Identity::query()->create([
            'email' => 'person@example.com',
            'password' => Hash::make('Correct-Horse-9!Battery'),
        ]);
        $client = $this->app->make(NativeOAuthClientManager::class)->ensure();

        $this->post('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $client->getKey(),
            'username' => $identity->email,
            'password' => 'Correct-Horse-9!Battery',
            'scope' => 'game:ticket',
        ])->assertStatus(400);
    }

    public function test_unregistered_scope_is_rejected_at_authorization_boundary(): void
    {
        $identity = Identity::query()->create([
            'email' => 'person@example.com',
            'password' => Hash::make('Correct-Horse-9!Battery'),
        ]);
        $client = $this->app->make(NativeOAuthClientManager::class)->ensure();
        $verifier = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
        $url = '/oauth/authorize?'.http_build_query([
            'client_id' => $client->getKey(),
            'redirect_uri' => 'http://127.0.0.1:49159/callback',
            'response_type' => 'code',
            'scope' => 'admin:all',
            'state' => 'invalid-scope-state',
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);

        $this->actingAs($identity, 'web')->get($url)
            ->assertStatus(400);
    }
}
