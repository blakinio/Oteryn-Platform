<?php

namespace Tests\Feature\GameAuth\Http;

use App\Accounts\Models\IdentityCanaryAccount;
use App\GameAuth\Tickets\GameLoginTicket;
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

    private const SERVICE_TOKEN = 'gateway-service-credential-with-at-least-32-bytes';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('game-auth.gateway.service_token_hashes', [hash('sha256', self::SERVICE_TOKEN)]);
    }

    public function test_authenticated_gateway_redeems_ticket_once_and_receives_minimum_authorization(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);

        $response = $this->redeem($issued->ticket);

        $response->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache')
            ->assertJsonPath('protocol_version', 1)
            ->assertJsonPath('authorization.canary_account_id', 1001)
            ->assertJsonPath('authorization.security_generation', 0)
            ->assertJsonMissingPath('authorization.identity_id')
            ->assertJsonStructure([
                'protocol_version',
                'authorization' => [
                    'canary_account_id',
                    'security_generation',
                    'redeemed_at',
                ],
            ]);

        self::assertNotNull(GameLoginTicket::query()->sole()->used_at);

        $replay = $this->redeem($issued->ticket);
        $replay->assertUnauthorized()
            ->assertJsonPath('error.code', 'invalid_ticket');
        self::assertStringNotContainsString($issued->ticket, $this->responseBody($replay->getContent()));
        self::assertStringNotContainsString(self::SERVICE_TOKEN, $this->responseBody($replay->getContent()));
    }

    public function test_invalid_service_credential_is_denied_without_consuming_ticket(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);

        $response = $this->postJson('/internal/v1/game-auth/tickets/redeem', [
            'protocol_version' => 1,
            'ticket' => $issued->ticket,
            'audience' => 'oteryn-game-gateway',
        ], [
            'Authorization' => 'Bearer invalid-gateway-credential-that-is-long-enough',
        ]);

        $response->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Bearer')
            ->assertJsonPath('error.code', 'unauthorized_service');
        self::assertNull(GameLoginTicket::query()->sole()->used_at);
        self::assertStringNotContainsString($issued->ticket, $this->responseBody($response->getContent()));
    }

    public function test_missing_service_hash_configuration_fails_closed(): void
    {
        config()->set('game-auth.gateway.service_token_hashes', []);
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);

        $response = $this->redeem($issued->ticket);

        $response->assertStatus(503)
            ->assertJsonPath('error.code', 'temporarily_unavailable');
        self::assertNull(GameLoginTicket::query()->sole()->used_at);
        self::assertStringNotContainsString($issued->ticket, $this->responseBody($response->getContent()));
        self::assertStringNotContainsString(self::SERVICE_TOKEN, $this->responseBody($response->getContent()));
    }

    public function test_wrong_audience_and_client_supplied_account_are_rejected_before_consume(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);

        $this->postJson('/internal/v1/game-auth/tickets/redeem', [
            'protocol_version' => 1,
            'ticket' => $issued->ticket,
            'audience' => 'other-consumer',
        ], $this->serviceHeaders())->assertUnprocessable()
            ->assertJsonValidationErrors(['audience']);

        $this->postJson('/internal/v1/game-auth/tickets/redeem', [
            'protocol_version' => 1,
            'ticket' => $issued->ticket,
            'audience' => 'oteryn-game-gateway',
            'canary_account_id' => 9999,
        ], $this->serviceHeaders())->assertUnprocessable()
            ->assertJsonValidationErrors(['canary_account_id']);

        self::assertNull(GameLoginTicket::query()->sole()->used_at);
    }

    public function test_invalid_protocol_and_malformed_ticket_fail_without_echoing_bearer_material(): void
    {
        $ticket = str_repeat('A', 43);
        $response = $this->postJson('/internal/v1/game-auth/tickets/redeem', [
            'protocol_version' => 2,
            'ticket' => $ticket.'!',
            'audience' => 'oteryn-game-gateway',
        ], $this->serviceHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['protocol_version', 'ticket']);
        self::assertStringNotContainsString($ticket.'!', $this->responseBody($response->getContent()));
        self::assertStringNotContainsString(self::SERVICE_TOKEN, $this->responseBody($response->getContent()));
    }

    /**
     * @return TestResponse<Response>
     */
    private function redeem(string $ticket): TestResponse
    {
        return $this->postJson('/internal/v1/game-auth/tickets/redeem', [
            'protocol_version' => 1,
            'ticket' => $ticket,
            'audience' => 'oteryn-game-gateway',
        ], $this->serviceHeaders());
    }

    /**
     * @return array<string, string>
     */
    private function serviceHeaders(): array
    {
        return ['Authorization' => 'Bearer '.self::SERVICE_TOKEN];
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
