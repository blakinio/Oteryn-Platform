<?php

namespace Tests\Feature\GameAuth\Http;

use App\Accounts\Models\IdentityCanaryAccount;
use App\GameAuth\OAuth\NativeOAuthClientManager;
use App\GameAuth\Tickets\GameLoginTicket;
use App\Identity\Models\Identity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Tests\Feature\GameAuth\OAuth\Concerns\ConfiguresEphemeralPassportKeys;
use Tests\TestCase;

final class GameLoginTicketIssuanceApiTest extends TestCase
{
    use ConfiguresEphemeralPassportKeys;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureEphemeralPassportKeys();
    }

    public function test_native_oauth_bootstrap_mints_one_ticket_and_revokes_access_and_refresh_tokens(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $client = $this->app->make(NativeOAuthClientManager::class)->ensure();
        $oauth = $this->oauthBootstrap($identity, $client, 'game:ticket');

        $response = $this->postJson('/api/v1/game-auth/tickets', [
            'protocol_version' => 1,
        ], [
            'Authorization' => 'Bearer '.$oauth['access_token'],
        ]);

        $response->assertCreated()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache')
            ->assertJsonPath('protocol_version', 1)
            ->assertJsonStructure(['protocol_version', 'ticket', 'expires_in']);

        $ticket = $response->json('ticket');
        $expiresIn = $response->json('expires_in');

        self::assertIsString($ticket);
        self::assertMatchesRegularExpression('/^[A-Za-z0-9_-]{43}$/', $ticket);
        self::assertIsInt($expiresIn);
        self::assertGreaterThanOrEqual(1, $expiresIn);
        self::assertLessThanOrEqual(60, $expiresIn);

        $stored = GameLoginTicket::query()->sole();
        self::assertSame(hash('sha256', $ticket), $stored->ticket_hash);
        self::assertSame($identity->id, $stored->identity_id);
        self::assertSame(1001, $stored->canary_account_id);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $oauth['access_token_id'],
            'revoked' => true,
        ]);
        $this->assertDatabaseHas('oauth_refresh_tokens', [
            'id' => $oauth['refresh_token_id'],
            'revoked' => true,
        ]);

        $this->postJson('/api/v1/game-auth/tickets', [
            'protocol_version' => 1,
        ], [
            'Authorization' => 'Bearer '.$oauth['access_token'],
        ])->assertUnauthorized();

        self::assertSame(1, GameLoginTicket::query()->count());
    }

    public function test_non_native_oauth_client_is_denied_without_consuming_its_token_family(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $this->app->make(NativeOAuthClientManager::class)->ensure();
        $otherClient = $this->app->make(ClientRepository::class)->createAuthorizationCodeGrantClient(
            name: 'Other Public Client',
            redirectUris: ['http://127.0.0.1/callback'],
            confidential: false,
        );
        $oauth = $this->oauthBootstrap($identity, $otherClient, 'game:ticket');

        $this->postJson('/api/v1/game-auth/tickets', [
            'protocol_version' => 1,
        ], [
            'Authorization' => 'Bearer '.$oauth['access_token'],
        ])->assertForbidden()
            ->assertJsonPath('error.code', 'invalid_oauth_bootstrap');

        self::assertSame(0, GameLoginTicket::query()->count());
        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $oauth['access_token_id'],
            'revoked' => false,
        ]);
        $this->assertDatabaseHas('oauth_refresh_tokens', [
            'id' => $oauth['refresh_token_id'],
            'revoked' => false,
        ]);
    }

    public function test_missing_game_ticket_scope_is_denied_without_consuming_token_family(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $client = $this->app->make(NativeOAuthClientManager::class)->ensure();
        $oauth = $this->oauthBootstrap($identity, $client, null);

        $this->postJson('/api/v1/game-auth/tickets', [
            'protocol_version' => 1,
        ], [
            'Authorization' => 'Bearer '.$oauth['access_token'],
        ])->assertForbidden()
            ->assertJsonPath('error.code', 'invalid_oauth_bootstrap');

        self::assertSame(0, GameLoginTicket::query()->count());
        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $oauth['access_token_id'],
            'revoked' => false,
        ]);
        $this->assertDatabaseHas('oauth_refresh_tokens', [
            'id' => $oauth['refresh_token_id'],
            'revoked' => false,
        ]);
    }

    public function test_client_supplied_account_ownership_is_rejected_without_revoking_valid_bootstrap(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $client = $this->app->make(NativeOAuthClientManager::class)->ensure();
        $oauth = $this->oauthBootstrap($identity, $client, 'game:ticket');

        $this->postJson('/api/v1/game-auth/tickets', [
            'protocol_version' => 1,
            'canary_account_id' => 9999,
        ], [
            'Authorization' => 'Bearer '.$oauth['access_token'],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['canary_account_id']);

        self::assertSame(0, GameLoginTicket::query()->count());
        $this->assertDatabaseHas('oauth_access_tokens', [
            'id' => $oauth['access_token_id'],
            'revoked' => false,
        ]);
        $this->assertDatabaseHas('oauth_refresh_tokens', [
            'id' => $oauth['refresh_token_id'],
            'revoked' => false,
        ]);
    }

    public function test_issuance_requires_a_valid_passport_bearer(): void
    {
        $this->postJson('/api/v1/game-auth/tickets', [
            'protocol_version' => 1,
        ])->assertUnauthorized();
    }

    /**
     * @return array{access_token: string, access_token_id: string, refresh_token_id: string}
     */
    private function oauthBootstrap(Identity $identity, Client $client, ?string $scope): array
    {
        $this->post('/login', [
            'email' => $identity->email,
            'password' => 'Correct-Horse-9!Battery',
        ])->assertRedirect(route('home'));
        [$verifier, $challenge] = $this->pkcePair();
        $redirectUri = 'http://127.0.0.1:'.random_int(49152, 60999).'/callback';
        $state = 'state-'.bin2hex(random_bytes(16));
        $query = [
            'client_id' => $client->getKey(),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'state' => $state,
            'code_challenge' => $challenge,
            'code_challenge_method' => 'S256',
        ];

        if ($scope !== null) {
            $query['scope'] = $scope;
        }

        $authorization = $this->get('/oauth/authorize?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986));
        $authorization->assertOk();
        preg_match('/name="auth_token" value="([^"]+)"/', $this->responseBody($authorization->getContent()), $matches);
        $authToken = $matches[1] ?? null;

        if (! is_string($authToken)) {
            self::fail('OAuth authorization view did not contain an auth_token value.');
        }

        $approval = $this->post(route('passport.authorizations.approve'), [
            'state' => $state,
            'client_id' => $client->getKey(),
            'auth_token' => html_entity_decode($authToken, ENT_QUOTES | ENT_HTML5),
        ]);
        $location = $approval->headers->get('Location');

        if (! is_string($location)) {
            self::fail('OAuth approval response did not contain a redirect location.');
        }

        parse_str((string) parse_url($location, PHP_URL_QUERY), $redirectQuery);
        $code = $redirectQuery['code'] ?? null;

        if (! is_string($code)) {
            self::fail('OAuth approval redirect did not contain an authorization code.');
        }

        $tokenResponse = $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $client->getKey(),
            'redirect_uri' => $redirectUri,
            'code' => $code,
            'code_verifier' => $verifier,
        ]);
        $tokenResponse->assertOk();
        $accessToken = $tokenResponse->json('access_token');

        if (! is_string($accessToken)) {
            self::fail('OAuth token response did not contain an access token.');
        }

        $access = DB::table('oauth_access_tokens')
            ->where('client_id', $client->getKey())
            ->where('user_id', (string) $identity->id)
            ->orderByDesc('created_at')
            ->first();

        if ($access === null || ! is_string($access->id)) {
            self::fail('OAuth access-token row was not persisted.');
        }

        $refresh = DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $access->id)
            ->first();

        if ($refresh === null || ! is_string($refresh->id)) {
            self::fail('OAuth refresh-token row was not persisted.');
        }

        return [
            'access_token' => $accessToken,
            'access_token_id' => $access->id,
            'refresh_token_id' => $refresh->id,
        ];
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

    private function createIdentity(): Identity
    {
        return Identity::query()->create([
            'email' => 'person-'.bin2hex(random_bytes(4)).'@example.com',
            'password' => Hash::make('Correct-Horse-9!Battery'),
        ]);
    }

    private function createReadyBinding(Identity $identity, int $canaryAccountId): IdentityCanaryAccount
    {
        return IdentityCanaryAccount::query()->create([
            'identity_id' => $identity->id,
            'canary_account_id' => $canaryAccountId,
            'provisioning_name' => 'ready_'.$identity->id,
            'canary_creation_epoch' => 1,
            'status' => IdentityCanaryAccount::STATUS_READY,
            'ready_at' => now(),
        ]);
    }

    private function responseBody(string|false $content): string
    {
        if (! is_string($content)) {
            self::fail('HTTP response body was not a string.');
        }

        return $content;
    }
}
