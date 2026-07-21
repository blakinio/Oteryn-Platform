<?php

namespace Tests\Feature\GameAuth\OAuth;

use App\GameAuth\OAuth\NativeOAuthClientManager;
use App\Identity\Models\Identity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

final class NativeOAuthPkceTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Correct-Horse-9!Battery';

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureEphemeralPassportKeys();
    }

    public function test_native_client_is_public_pkce_capable_and_idempotent(): void
    {
        $manager = $this->app->make(NativeOAuthClientManager::class);

        $first = $manager->ensure();
        $second = $manager->ensure();

        self::assertSame($first->getKey(), $second->getKey());
        self::assertFalse($first->confidential());
        self::assertNull($first->secret);
        self::assertNull($first->user_id);
        self::assertTrue($first->hasGrantType('authorization_code'));
        self::assertSame(['http://127.0.0.1/callback'], $first->redirectUris());
        self::assertSame(1, Client::query()->count());
    }

    public function test_dynamic_loopback_port_authorization_and_pkce_s256_token_exchange_succeed_without_client_secret(): void
    {
        $identity = $this->createIdentity();
        $client = $this->app->make(NativeOAuthClientManager::class)->ensure();
        [$verifier, $challenge] = $this->pkcePair();
        $redirectUri = 'http://127.0.0.1:49152/callback';
        $state = 'state-'.bin2hex(random_bytes(16));
        $authorizationUrl = $this->authorizationUrl($client, $redirectUri, $challenge, $state);

        $authorization = $this->actingAs($identity, 'web')->get($authorizationUrl);

        $authorization->assertOk();
        $authorization->assertSee('Authorize Oteryn game login');
        $authorization->assertSee('Request a one-time Oteryn game login ticket.');

        $authToken = $this->extractAuthToken($authorization->getContent());
        $approval = $this->actingAs($identity, 'web')->post(route('passport.authorizations.approve'), [
            'state' => $state,
            'client_id' => $client->getKey(),
            'auth_token' => $authToken,
        ]);

        $location = $approval->headers->get('Location');
        self::assertIsString($location);
        self::assertStringStartsWith($redirectUri, $location);
        $query = $this->redirectQuery($location);
        self::assertSame($state, $query['state'] ?? null);
        self::assertArrayHasKey('code', $query);

        $token = $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $client->getKey(),
            'redirect_uri' => $redirectUri,
            'code' => $query['code'],
            'code_verifier' => $verifier,
        ]);

        $token->assertOk()
            ->assertJsonStructure([
                'token_type',
                'expires_in',
                'access_token',
                'refresh_token',
            ]);
        self::assertLessThanOrEqual(300, $token->json('expires_in'));

        $access = DB::table('oauth_access_tokens')
            ->where('client_id', $client->getKey())
            ->where('user_id', (string) $identity->id)
            ->first();
        self::assertNotNull($access);
        self::assertNotNull($access->expires_at);
    }

    public function test_wrong_pkce_verifier_fails_closed(): void
    {
        $identity = $this->createIdentity();
        $client = $this->app->make(NativeOAuthClientManager::class)->ensure();
        [, $challenge] = $this->pkcePair();
        $redirectUri = 'http://127.0.0.1:49153/callback';
        $state = 'state-'.bin2hex(random_bytes(16));
        $code = $this->authorize($identity, $client, $redirectUri, $challenge, $state);

        $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $client->getKey(),
            'redirect_uri' => $redirectUri,
            'code' => $code,
            'code_verifier' => 'definitely-the-wrong-verifier-value',
        ])->assertStatus(400);

        self::assertSame(0, DB::table('oauth_access_tokens')->count());
    }

    public function test_missing_pkce_verifier_fails_closed(): void
    {
        $identity = $this->createIdentity();
        $client = $this->app->make(NativeOAuthClientManager::class)->ensure();
        [, $challenge] = $this->pkcePair();
        $redirectUri = 'http://127.0.0.1:49154/callback';
        $state = 'state-'.bin2hex(random_bytes(16));
        $code = $this->authorize($identity, $client, $redirectUri, $challenge, $state);

        $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $client->getKey(),
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ])->assertStatus(400);

        self::assertSame(0, DB::table('oauth_access_tokens')->count());
    }

    public function test_wrong_loopback_path_and_non_loopback_redirect_are_rejected(): void
    {
        $identity = $this->createIdentity();
        $client = $this->app->make(NativeOAuthClientManager::class)->ensure();
        [, $challenge] = $this->pkcePair();

        $this->actingAs($identity, 'web')->get(
            $this->authorizationUrl($client, 'http://127.0.0.1:49155/not-callback', $challenge, 'wrong-path-state'),
        )->assertStatus(400);

        $this->actingAs($identity, 'web')->get(
            $this->authorizationUrl($client, 'https://example.com/callback', $challenge, 'remote-state'),
        )->assertStatus(400);
    }

    public function test_unauthenticated_authorization_returns_through_existing_identity_login(): void
    {
        $identity = $this->createIdentity();
        $client = $this->app->make(NativeOAuthClientManager::class)->ensure();
        [, $challenge] = $this->pkcePair();
        $authorizationUrl = $this->authorizationUrl(
            $client,
            'http://127.0.0.1:49156/callback',
            $challenge,
            'browser-login-state',
        );

        $this->get($authorizationUrl)
            ->assertRedirect(route('identity.login.create'));

        $this->post('/login', [
            'email' => $identity->email,
            'password' => self::PASSWORD,
        ])->assertRedirect($authorizationUrl);
    }

    public function test_mfa_challenge_preserves_interrupted_oauth_authorization_request(): void
    {
        $identity = $this->createIdentity(withMfa: true);
        $client = $this->app->make(NativeOAuthClientManager::class)->ensure();
        [, $challenge] = $this->pkcePair();
        $authorizationUrl = $this->authorizationUrl(
            $client,
            'http://127.0.0.1:49157/callback',
            $challenge,
            'mfa-browser-state',
        );

        $this->get($authorizationUrl)
            ->assertRedirect(route('identity.login.create'));

        $this->post('/login', [
            'email' => $identity->email,
            'password' => self::PASSWORD,
        ])->assertRedirect(route('identity.mfa.challenge.create'));

        $secret = $identity->fresh()?->two_factor_secret;
        self::assertIsString($secret);
        $code = (new Google2FA)->getCurrentOtp($secret);

        $this->post('/mfa/challenge', [
            'code' => $code,
        ])->assertRedirect($authorizationUrl);
    }

    private function authorize(
        Identity $identity,
        Client $client,
        string $redirectUri,
        string $challenge,
        string $state,
    ): string {
        $authorization = $this->actingAs($identity, 'web')->get(
            $this->authorizationUrl($client, $redirectUri, $challenge, $state),
        );
        $authorization->assertOk();

        $approval = $this->actingAs($identity, 'web')->post(route('passport.authorizations.approve'), [
            'state' => $state,
            'client_id' => $client->getKey(),
            'auth_token' => $this->extractAuthToken($authorization->getContent()),
        ]);

        $location = $approval->headers->get('Location');
        self::assertIsString($location);
        $query = $this->redirectQuery($location);
        $code = $query['code'] ?? null;
        self::assertIsString($code);

        return $code;
    }

    private function authorizationUrl(Client $client, string $redirectUri, string $challenge, string $state): string
    {
        return '/oauth/authorize?'.http_build_query([
            'client_id' => $client->getKey(),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'game:ticket',
            'state' => $state,
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);
    }

    private function extractAuthToken(string $html): string
    {
        preg_match('/name="auth_token" value="([^"]+)"/', $html, $matches);
        $authToken = $matches[1] ?? null;
        self::assertIsString($authToken);

        return html_entity_decode($authToken, ENT_QUOTES | ENT_HTML5);
    }

    /**
     * @return array<string, string>
     */
    private function redirectQuery(string $location): array
    {
        $queryString = parse_url($location, PHP_URL_QUERY);
        self::assertIsString($queryString);
        parse_str($queryString, $query);

        return array_filter($query, 'is_string');
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function pkcePair(): array
    {
        $verifier = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        return [$verifier, $challenge];
    }

    private function createIdentity(bool $withMfa = false): Identity
    {
        $identity = Identity::query()->create([
            'email' => 'person@example.com',
            'password' => Hash::make(self::PASSWORD),
        ]);

        if ($withMfa) {
            $identity->forceFill([
                'two_factor_secret' => (new Google2FA)->generateSecretKey(),
                'two_factor_recovery_codes' => [],
                'two_factor_confirmed_at' => now(),
                'two_factor_last_used_timestep' => null,
            ])->save();
        }

        return $identity;
    }

    private function configureEphemeralPassportKeys(): void
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        self::assertNotFalse($key);

        $privateKey = '';
        self::assertTrue(openssl_pkey_export($key, $privateKey));
        $details = openssl_pkey_get_details($key);
        self::assertIsArray($details);
        self::assertArrayHasKey('key', $details);
        self::assertIsString($details['key']);

        config([
            'passport.private_key' => $privateKey,
            'passport.public_key' => $details['key'],
        ]);
    }
}
