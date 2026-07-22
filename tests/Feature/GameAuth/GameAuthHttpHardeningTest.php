<?php

namespace Tests\Feature\GameAuth;

use App\Accounts\Models\IdentityCanaryAccount;
use App\GameAuth\Tickets\IssueGameLoginTicket;
use App\Identity\Models\Identity;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class GameAuthHttpHardeningTest extends TestCase
{
    use RefreshDatabase;

    private const SERVICE_CREDENTIAL = 'gateway-service-credential-with-sufficient-entropy';

    private const ROTATED_SERVICE_CREDENTIAL = 'rotated-gateway-service-credential-with-sufficient-entropy';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'game-auth.gateway.service_token_sha256s' => [hash('sha256', self::SERVICE_CREDENTIAL)],
            'game-auth.gateway.service_token_sha256' => '',
        ]);
    }

    public function test_all_game_auth_credential_boundaries_disable_response_caching(): void
    {
        $this->postJson('/api/v1/game-auth/tickets', ['protocol_version' => 1])
            ->assertUnauthorized()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache');

        $this->withToken('invalid-gateway-credential-with-sufficient-length')
            ->postJson('/internal/v1/game-auth/tickets/redeem', [])
            ->assertUnauthorized()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache');

        $this->withToken('invalid-gateway-credential-with-sufficient-length')
            ->postJson('/internal/v1/game-auth/login-context', [])
            ->assertUnauthorized()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache');
    }

    public function test_overlapping_gateway_credentials_support_rotation_without_downtime(): void
    {
        config([
            'game-auth.gateway.service_token_sha256s' => [
                hash('sha256', self::SERVICE_CREDENTIAL),
                hash('sha256', self::ROTATED_SERVICE_CREDENTIAL),
            ],
            'game-auth.gateway.service_token_sha256' => '',
        ]);
        $identity = $this->createIdentityWithReadyBinding(1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);

        $this->withToken(self::ROTATED_SERVICE_CREDENTIAL)
            ->postJson('/internal/v1/game-auth/tickets/redeem', [
                'protocol_version' => 1,
                'ticket' => $issued->ticket,
                'audience' => 'oteryn-game-gateway',
            ])
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache');
    }

    public function test_legacy_single_hash_configuration_remains_accepted_during_migration(): void
    {
        config([
            'game-auth.gateway.service_token_sha256s' => [],
            'game-auth.gateway.service_token_sha256' => hash('sha256', self::SERVICE_CREDENTIAL),
        ]);
        $identity = $this->createIdentityWithReadyBinding(1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);

        $this->withToken(self::SERVICE_CREDENTIAL)
            ->postJson('/internal/v1/game-auth/tickets/redeem', [
                'protocol_version' => 1,
                'ticket' => $issued->ticket,
                'audience' => 'oteryn-game-gateway',
            ])
            ->assertOk();
    }

    public function test_invalid_gateway_credentials_are_throttled_before_service_authentication(): void
    {
        RateLimiter::for(
            'game-auth-ticket-redeem',
            static fn (Request $request): Limit => Limit::perMinute(1)->by('redeem-invalid-service-test'),
        );
        RateLimiter::for(
            'game-auth-login-context',
            static fn (Request $request): Limit => Limit::perMinute(1)->by('context-invalid-service-test'),
        );

        $this->withToken('first-invalid-gateway-credential-long-enough')
            ->postJson('/internal/v1/game-auth/tickets/redeem', [])
            ->assertUnauthorized();
        $this->withToken('second-invalid-gateway-credential-long-enough')
            ->postJson('/internal/v1/game-auth/tickets/redeem', [])
            ->assertTooManyRequests()
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->withToken('first-invalid-context-credential-long-enough')
            ->postJson('/internal/v1/game-auth/login-context', [])
            ->assertUnauthorized();
        $this->withToken('second-invalid-context-credential-long-enough')
            ->postJson('/internal/v1/game-auth/login-context', [])
            ->assertTooManyRequests()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_empty_or_malformed_gateway_hash_configuration_fails_closed(): void
    {
        config([
            'game-auth.gateway.service_token_sha256s' => ['not-a-sha256-hash'],
            'game-auth.gateway.service_token_sha256' => '',
        ]);

        $this->withToken(self::SERVICE_CREDENTIAL)
            ->postJson('/internal/v1/game-auth/tickets/redeem', [])
            ->assertServiceUnavailable()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('error', 'service_unavailable');
    }

    private function createIdentityWithReadyBinding(int $canaryAccountId): Identity
    {
        $identity = Identity::query()->create([
            'email' => 'hardening-'.bin2hex(random_bytes(4)).'@example.com',
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
