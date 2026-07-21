<?php

namespace Tests\Feature\GameAuth\OAuth;

use App\GameAuth\OAuth\NativeOAuthClientManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use LogicException;
use Tests\TestCase;

final class NativeOAuthClientManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ensure_command_is_registered_and_idempotently_creates_one_public_client(): void
    {
        $this->artisan('game-auth:oauth-client:ensure')
            ->assertSuccessful();

        $this->artisan('game-auth:oauth-client:ensure')
            ->assertSuccessful();

        $client = Client::query()->sole();

        self::assertFalse($client->confidential());
        self::assertNull($client->getAttribute('secret'));
        self::assertTrue($client->hasGrantType('authorization_code'));
        self::assertSame(['http://127.0.0.1/callback'], $client->redirectUris());
    }

    public function test_native_redirect_configuration_fails_closed_if_it_is_not_exact_loopback_contract(): void
    {
        config(['game-auth.oauth.native_redirect_uri' => 'http://localhost/callback']);

        $this->expectException(LogicException::class);
        $this->app->make(NativeOAuthClientManager::class)->ensure();
    }

    public function test_existing_confidential_client_with_native_name_is_rejected(): void
    {
        Client::query()->create([
            'id' => 'confidential-native-client',
            'user_id' => null,
            'name' => 'Oteryn OTClient',
            'secret' => 'not-a-real-secret',
            'provider' => null,
            'redirect_uris' => ['http://127.0.0.1/callback'],
            'grant_types' => ['authorization_code'],
            'revoked' => false,
        ]);

        $this->expectException(LogicException::class);
        $this->app->make(NativeOAuthClientManager::class)->ensure();
    }
}
