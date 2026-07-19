<?php

namespace Tests\Feature\Identity\Mfa;

use App\Audit\SecurityEventRecorder;
use App\Identity\Actions\RevokeIdentityWebSessions;
use App\Identity\Mfa\ResetIdentityMfa;
use App\Identity\Models\Identity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class MfaStateFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mfa_secret_and_recovery_state_are_encrypted_at_rest_and_hidden_from_serialization(): void
    {
        $identity = $this->createIdentity();
        $secret = 'TEST-MFA-SECRET-NOT-REAL';
        $recoveryCodes = ['recovery-alpha', 'recovery-bravo'];

        $identity->forceFill([
            'mfa_secret' => $secret,
            'mfa_recovery_codes' => $recoveryCodes,
            'mfa_confirmed_at' => now(),
        ])->save();

        $rawSecret = DB::table('identities')
            ->where('id', $identity->id)
            ->value('mfa_secret');
        $rawRecoveryCodes = DB::table('identities')
            ->where('id', $identity->id)
            ->value('mfa_recovery_codes');

        self::assertIsString($rawSecret);
        self::assertIsString($rawRecoveryCodes);
        self::assertNotSame($secret, $rawSecret);
        self::assertStringNotContainsString('recovery-alpha', $rawRecoveryCodes);
        self::assertStringNotContainsString('recovery-bravo', $rawRecoveryCodes);

        $fresh = Identity::query()->findOrFail($identity->id);
        self::assertSame($secret, $fresh->mfa_secret);
        self::assertSame($recoveryCodes, $fresh->mfa_recovery_codes);
        self::assertTrue($fresh->hasConfirmedMfa());

        $serialized = $fresh->toArray();
        self::assertArrayNotHasKey('mfa_secret', $serialized);
        self::assertArrayNotHasKey('mfa_recovery_codes', $serialized);
    }

    public function test_confirmed_mfa_state_fails_closed_when_secret_or_confirmation_is_missing(): void
    {
        $identity = $this->createIdentity();
        self::assertFalse($identity->hasConfirmedMfa());

        $identity->forceFill([
            'mfa_secret' => 'TEST-MFA-SECRET-NOT-REAL',
            'mfa_confirmed_at' => null,
        ])->save();
        $identity->refresh();
        self::assertFalse($identity->hasConfirmedMfa());

        $identity->forceFill([
            'mfa_secret' => null,
            'mfa_confirmed_at' => now(),
        ])->save();
        $identity->refresh();
        self::assertFalse($identity->hasConfirmedMfa());
    }

    public function test_internal_mfa_reset_clears_state_and_revokes_existing_platform_web_session(): void
    {
        $identity = $this->createIdentity();
        $identity->forceFill([
            'mfa_secret' => 'TEST-MFA-SECRET-NOT-REAL',
            'mfa_recovery_codes' => ['recovery-alpha', 'recovery-bravo'],
            'mfa_confirmed_at' => now(),
        ])->save();

        $this->post('/login', [
            'email' => $identity->email,
            'password' => 'Correct-Horse-9!Battery',
        ])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($identity, 'web');

        $securityEvents = new SecurityEventRecorder();
        $resetMfa = new ResetIdentityMfa(
            new RevokeIdentityWebSessions($securityEvents),
            $securityEvents,
        );

        $resetMfa->execute($identity);

        $fresh = Identity::query()->findOrFail($identity->id);
        self::assertNull($fresh->mfa_secret);
        self::assertNull($fresh->mfa_recovery_codes);
        self::assertNull($fresh->mfa_confirmed_at);
        self::assertFalse($fresh->hasConfirmedMfa());
        self::assertSame(1, $fresh->web_session_generation);

        $this->assertDatabaseHas('identity_security_events', [
            'identity_id' => $identity->id,
            'event_type' => SecurityEventRecorder::IDENTITY_MFA_RESET,
        ]);
        $this->assertDatabaseHas('identity_security_events', [
            'identity_id' => $identity->id,
            'event_type' => SecurityEventRecorder::IDENTITY_WEB_SESSIONS_REVOKED,
        ]);

        $this->get('/')->assertOk();
        $this->assertGuest('web');
    }

    private function createIdentity(): Identity
    {
        return Identity::query()->create([
            'email' => 'person@example.com',
            'password' => Hash::make('Correct-Horse-9!Battery'),
        ]);
    }
}
