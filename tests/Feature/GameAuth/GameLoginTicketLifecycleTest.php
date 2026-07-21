<?php

namespace Tests\Feature\GameAuth;

use App\Accounts\Models\IdentityCanaryAccount;
use App\Audit\SecurityEventRecorder;
use App\GameAuth\Tickets\GameLoginTicket;
use App\GameAuth\Tickets\GameLoginTicketDenied;
use App\GameAuth\Tickets\GameLoginTicketSecrets;
use App\GameAuth\Tickets\IssueGameLoginTicket;
use App\GameAuth\Tickets\RedeemGameLoginTicket;
use App\Identity\Models\Identity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class GameLoginTicketLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_returns_opaque_ticket_and_persists_only_hash_with_bound_authorization_state(): void
    {
        $this->travelTo(now()->startOfSecond());
        $identity = $this->createIdentity();
        $binding = $this->createReadyBinding($identity, 1001);

        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);
        $stored = GameLoginTicket::query()->firstOrFail();

        self::assertNotSame($issued->ticket, $stored->ticket_hash);
        self::assertSame(hash('sha256', $issued->ticket), $stored->ticket_hash);
        self::assertSame(64, strlen($stored->ticket_hash));
        self::assertArrayNotHasKey('ticket', $stored->getAttributes());
        self::assertSame($identity->id, $stored->identity_id);
        self::assertSame($binding->canary_account_id, $stored->canary_account_id);
        self::assertSame('oteryn-game-gateway', $stored->audience);
        self::assertSame(0, $stored->security_generation);
        self::assertTrue($stored->expires_at->equalTo(now()->addSeconds(60)));
        self::assertNull($stored->used_at);

        $this->assertDatabaseHas('identity_security_events', [
            'identity_id' => $identity->id,
            'event_type' => SecurityEventRecorder::GAME_LOGIN_TICKET_ISSUED,
        ]);
    }

    public function test_ticket_secret_contains_at_least_256_bits_of_random_source_entropy(): void
    {
        $ticket = (new GameLoginTicketSecrets)->generate();
        $padding = str_repeat('=', (4 - strlen($ticket) % 4) % 4);
        $decoded = base64_decode(strtr($ticket.$padding, '-_', '+/'), true);

        if (! is_string($decoded)) {
            self::fail('Generated ticket is not valid base64url data.');
        }

        self::assertSame(32, strlen($decoded));
    }

    public function test_issue_fails_closed_for_disabled_identity(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $identity->forceFill(['disabled_at' => now()])->save();

        $this->expectException(GameLoginTicketDenied::class);
        $this->app->make(IssueGameLoginTicket::class)->execute($identity);
    }

    public function test_issue_fails_closed_without_ready_canary_binding(): void
    {
        $identity = $this->createIdentity();
        IdentityCanaryAccount::query()->create([
            'identity_id' => $identity->id,
            'canary_account_id' => null,
            'provisioning_name' => 'pending_account',
            'canary_creation_epoch' => 1,
            'status' => IdentityCanaryAccount::STATUS_PENDING,
        ]);

        $this->expectException(GameLoginTicketDenied::class);
        $this->app->make(IssueGameLoginTicket::class)->execute($identity);
    }

    public function test_issue_fails_closed_for_conflict_binding(): void
    {
        $identity = $this->createIdentity();
        IdentityCanaryAccount::query()->create([
            'identity_id' => $identity->id,
            'canary_account_id' => null,
            'provisioning_name' => 'conflict_account',
            'canary_creation_epoch' => 1,
            'status' => IdentityCanaryAccount::STATUS_CONFLICT,
        ]);

        $this->expectException(GameLoginTicketDenied::class);
        $this->app->make(IssueGameLoginTicket::class)->execute($identity);
    }

    public function test_redeem_consumes_ticket_once_and_returns_exact_bound_account(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);

        $redeemed = $this->app->make(RedeemGameLoginTicket::class)
            ->execute($issued->ticket, 'oteryn-game-gateway');

        self::assertSame($identity->id, $redeemed->identityId);
        self::assertSame(1001, $redeemed->canaryAccountId);
        self::assertSame(0, $redeemed->securityGeneration);
        self::assertNotNull(GameLoginTicket::query()->firstOrFail()->used_at);
        $this->assertDatabaseHas('identity_security_events', [
            'identity_id' => $identity->id,
            'event_type' => SecurityEventRecorder::GAME_LOGIN_TICKET_REDEEMED,
        ]);

        $this->expectException(GameLoginTicketDenied::class);
        $this->app->make(RedeemGameLoginTicket::class)
            ->execute($issued->ticket, 'oteryn-game-gateway');
    }

    public function test_wrong_audience_is_denied_without_consuming_ticket(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);

        try {
            $this->app->make(RedeemGameLoginTicket::class)
                ->execute($issued->ticket, 'wrong-audience');
            self::fail('Wrong audience unexpectedly redeemed a ticket.');
        } catch (GameLoginTicketDenied) {
            self::assertNull(GameLoginTicket::query()->firstOrFail()->used_at);
        }

        $redeemed = $this->app->make(RedeemGameLoginTicket::class)
            ->execute($issued->ticket, 'oteryn-game-gateway');

        self::assertSame(1001, $redeemed->canaryAccountId);
    }

    public function test_expired_ticket_is_denied_without_consumption(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);
        $this->travel(61)->seconds();

        try {
            $this->app->make(RedeemGameLoginTicket::class)
                ->execute($issued->ticket, 'oteryn-game-gateway');
            self::fail('Expired ticket unexpectedly redeemed.');
        } catch (GameLoginTicketDenied) {
            self::assertNull(GameLoginTicket::query()->firstOrFail()->used_at);
        }
    }

    public function test_security_generation_change_invalidates_pending_ticket(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);
        Identity::query()->whereKey($identity->id)->increment('game_auth_generation');

        $this->expectException(GameLoginTicketDenied::class);
        $this->app->make(RedeemGameLoginTicket::class)
            ->execute($issued->ticket, 'oteryn-game-gateway');
    }

    public function test_identity_disable_after_issue_invalidates_pending_ticket(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);
        $identity->forceFill(['disabled_at' => now()])->save();

        $this->expectException(GameLoginTicketDenied::class);
        $this->app->make(RedeemGameLoginTicket::class)
            ->execute($issued->ticket, 'oteryn-game-gateway');
    }

    public function test_binding_drift_after_issue_invalidates_pending_ticket(): void
    {
        $identity = $this->createIdentity();
        $this->createReadyBinding($identity, 1001);
        $issued = $this->app->make(IssueGameLoginTicket::class)->execute($identity);
        IdentityCanaryAccount::query()
            ->whereKey($identity->id)
            ->update(['canary_account_id' => 1002]);

        $this->expectException(GameLoginTicketDenied::class);
        $this->app->make(RedeemGameLoginTicket::class)
            ->execute($issued->ticket, 'oteryn-game-gateway');
    }

    private function createIdentity(): Identity
    {
        return Identity::query()->create([
            'email' => 'person@example.com',
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
}
