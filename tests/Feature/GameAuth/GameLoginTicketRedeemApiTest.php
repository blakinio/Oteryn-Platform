<?php

namespace Tests\Feature\GameAuth;

use App\Accounts\Models\IdentityCanaryAccount;
use App\GameAuth\Tickets\IssueGameLoginTicket;
use App\Identity\Models\Identity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class GameLoginTicketRedeemApiTest extends TestCase
{
    use RefreshDatabase;

    private const SERVICE_CREDENTIAL = 'test-gateway-service-credential-with-sufficient-entropy';

    private const PREVIOUS_SERVICE_CREDENTIAL = 'previous-gateway-service-credential-with-sufficient-entropy';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'game-auth.gateway.service_token_sha256' => hash('sha256', self::SERVICE_CREDENTIAL),
            'game-auth.gateway.previous_service_token_sha256' => null,
        ]);
    }

    public function test_authenticated_gateway_redeems_ticket_once_and_receives_only_bounded_authorization_data(): void
    {
        $identity = $this->createIdentityWithReadyBinding(1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);

        $response = $this->withToken(self::SERVICE_CREDENTIAL)
            ->postJson('/internal/v1/game-auth/tickets/redeem', [
                'protocol_version' => 1,
                'ticket' => $issued->ticket,
                'audience' => 'oteryn-game-gateway',
            ]);

        $response->assertOk()
            ->assertJsonPath('protocol_version', 1)
            ->assertJsonPath('authorization.canary_account_id', 1001)
            ->assertJsonPath('authorization.security_generation', 0)
            ->assertJsonStructure(['authorization' => ['redeemed_at']]);
        $this->assertSensitiveResponseIsNotCacheable($response);

        $payload = $response->json();

        if (! is_array($payload)) {
            self::fail('Private redeem response was not a JSON object.');
        }

        $authorization = $payload['authorization'] ?? null;

        if (! is_array($authorization)) {
            self::fail('Private redeem response did not contain an authorization object.');
        }

        self::assertArrayNotHasKey('password', $payload);
        self::assertArrayNotHasKey('oauth_token', $payload);
        self::assertArrayNotHasKey('ticket', $payload);
        self::assertArrayNotHasKey('identity_id', $authorization);

        $replay = $this->withToken(self::SERVICE_CREDENTIAL)
            ->postJson('/internal/v1/game-auth/tickets/redeem', [
                'protocol_version' => 1,
                'ticket' => $issued->ticket,
                'audience' => 'oteryn-game-gateway',
            ]);
        $replay->assertStatus(401)
            ->assertJsonPath('error', 'invalid_ticket');
        $this->assertSensitiveResponseIsNotCacheable($replay);
    }

    public function test_overlapping_previous_gateway_service_credential_is_accepted_during_rotation(): void
    {
        config([
            'game-auth.gateway.previous_service_token_sha256' => hash('sha256', self::PREVIOUS_SERVICE_CREDENTIAL),
        ]);

        $identity = $this->createIdentityWithReadyBinding(1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);

        $response = $this->withToken(self::PREVIOUS_SERVICE_CREDENTIAL)
            ->postJson('/internal/v1/game-auth/tickets/redeem', [
                'protocol_version' => 1,
                'ticket' => $issued->ticket,
                'audience' => 'oteryn-game-gateway',
            ]);

        $response->assertOk()
            ->assertJsonPath('authorization.canary_account_id', 1001);
        $this->assertSensitiveResponseIsNotCacheable($response);
    }

    public function test_invalid_or_missing_gateway_service_credential_is_denied(): void
    {
        $wrongCredential = $this->withToken('wrong-credential')
            ->postJson('/internal/v1/game-auth/tickets/redeem', [
                'protocol_version' => 1,
                'ticket' => 'not-a-real-ticket',
                'audience' => 'oteryn-game-gateway',
            ]);
        $wrongCredential->assertStatus(401)
            ->assertJsonPath('error', 'unauthorized_service');
        $this->assertSensitiveResponseIsNotCacheable($wrongCredential);

        $missingCredential = $this->postJson('/internal/v1/game-auth/tickets/redeem', [
            'protocol_version' => 1,
            'ticket' => 'not-a-real-ticket',
            'audience' => 'oteryn-game-gateway',
        ]);
        $missingCredential->assertStatus(401);
        $this->assertSensitiveResponseIsNotCacheable($missingCredential);
    }

    public function test_unauthorized_redeem_attempts_are_source_throttled_before_service_authentication(): void
    {
        for ($attempt = 1; $attempt <= 60; $attempt++) {
            $response = $this->withToken('wrong-credential-'.$attempt)
                ->postJson('/internal/v1/game-auth/tickets/redeem', [
                    'protocol_version' => 1,
                    'ticket' => 'not-a-real-ticket',
                    'audience' => 'oteryn-game-gateway',
                ]);

            $response->assertStatus(401)
                ->assertJsonPath('error', 'unauthorized_service');
        }

        $throttled = $this->withToken('wrong-credential-61')
            ->postJson('/internal/v1/game-auth/tickets/redeem', [
                'protocol_version' => 1,
                'ticket' => 'not-a-real-ticket',
                'audience' => 'oteryn-game-gateway',
            ]);

        $throttled->assertStatus(429);
        $this->assertSensitiveResponseIsNotCacheable($throttled);
    }

    public function test_missing_or_invalid_service_hash_configuration_fails_closed(): void
    {
        foreach ([
            ['game-auth.gateway.service_token_sha256' => null],
            ['game-auth.gateway.previous_service_token_sha256' => 'not-a-sha256-hash'],
        ] as $configuration) {
            config($configuration);

            $response = $this->withToken(self::SERVICE_CREDENTIAL)
                ->postJson('/internal/v1/game-auth/tickets/redeem', [
                    'protocol_version' => 1,
                    'ticket' => 'not-a-real-ticket',
                    'audience' => 'oteryn-game-gateway',
                ]);

            $response->assertStatus(503)
                ->assertJsonPath('error', 'service_unavailable');
            $this->assertSensitiveResponseIsNotCacheable($response);

            config([
                'game-auth.gateway.service_token_sha256' => hash('sha256', self::SERVICE_CREDENTIAL),
                'game-auth.gateway.previous_service_token_sha256' => null,
            ]);
        }
    }

    public function test_wrong_audience_and_client_supplied_account_identifiers_fail_closed(): void
    {
        $identity = $this->createIdentityWithReadyBinding(1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);

        $response = $this->withToken(self::SERVICE_CREDENTIAL)
            ->postJson('/internal/v1/game-auth/tickets/redeem', [
                'protocol_version' => 1,
                'ticket' => $issued->ticket,
                'audience' => 'wrong-audience',
                'canary_account_id' => 999,
            ]);

        $response->assertStatus(422);
        $this->assertSensitiveResponseIsNotCacheable($response);
    }

    /**
     * @param  TestResponse<Response>  $response
     */
    private function assertSensitiveResponseIsNotCacheable(TestResponse $response): void
    {
        $cacheControl = (string) $response->headers->get('Cache-Control');

        self::assertStringContainsString('no-store', $cacheControl);
        self::assertStringContainsString('no-cache', $cacheControl);
        self::assertStringContainsString('must-revalidate', $cacheControl);
        self::assertStringContainsString('private', $cacheControl);
        $response->assertHeader('Pragma', 'no-cache')
            ->assertHeader('Expires', '0');
    }

    private function createIdentityWithReadyBinding(int $canaryAccountId): Identity
    {
        $identity = Identity::query()->create([
            'email' => 'person@example.com',
            'password' => Hash::make('Correct-Horse-9!Battery'),
        ]);

        IdentityCanaryAccount::query()->create([
            'identity_id' => $identity->id,
            'canary_account_id' => $canaryAccountId,
            'provisioning_name' => 'ready_'.$identity->id,
            'canary_creation_epoch' => 1,
            'status' => IdentityCanaryAccount::STATUS_READY,
            'ready_at' => now(),
        ]);

        return $identity;
    }
}
