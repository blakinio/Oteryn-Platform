<?php

namespace Tests\Feature\Admin;

use App\Admin\AdminRoleManager;
use App\Audit\AdminAuditRecorder;
use App\Identity\Models\Identity;
use App\Identity\Sessions\WebSessionState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_view_requires_explicit_audit_permission(): void
    {
        $actor = $this->createIdentity('content-editor@example.com');
        $this->assignRole($actor, AdminRoleManager::CONTENT_EDITOR);
        $this->actingAsCurrent($actor);

        $this->get(route('admin.audit.index'))->assertForbidden();
    }

    public function test_audit_view_requires_confirmed_mfa(): void
    {
        $actor = $this->createIdentity('security-no-mfa@example.com', false);
        $this->assignRole($actor, AdminRoleManager::SECURITY_ADMIN);
        $this->actingAsCurrent($actor);

        $this->get(route('admin.audit.index'))->assertForbidden();
    }

    public function test_security_admin_can_view_bounded_audit_pages(): void
    {
        $actor = $this->createIdentity('security-audit@example.com');
        $this->assignRole($actor, AdminRoleManager::SECURITY_ADMIN);

        $recorder = app(AdminAuditRecorder::class);
        for ($index = 1; $index <= 51; $index++) {
            $recorder->record(
                $actor->id,
                sprintf('test.audit.%03d', $index),
                'test_target',
                (string) $index,
            );
        }

        $this->actingAsCurrent($actor);

        $this->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSeeText('test.audit.051')
            ->assertDontSeeText('test.audit.001');

        $this->get(route('admin.audit.index', ['page' => 2]))
            ->assertOk()
            ->assertSeeText('test.audit.001')
            ->assertDontSeeText('test.audit.051');
    }

    private function createIdentity(string $email, bool $confirmedMfa = true): Identity
    {
        $identity = Identity::query()->create([
            'email' => $email,
            'password' => Hash::make('Correct-Horse-9!Battery'),
        ]);

        if ($confirmedMfa) {
            $identity->forceFill([
                'two_factor_secret' => 'TEST-MFA-SECRET-NOT-REAL',
                'two_factor_confirmed_at' => now(),
            ])->save();
        }

        return $identity;
    }

    private function assignRole(Identity $identity, string $roleKey): void
    {
        $roleId = DB::table('admin_roles')->where('key', $roleKey)->value('id');

        if (! is_int($roleId) && ! (is_string($roleId) && ctype_digit($roleId))) {
            self::fail('Expected an integer-compatible administrator role id.');
        }

        DB::table('identity_admin_roles')->insert([
            'identity_id' => $identity->id,
            'role_id' => (int) $roleId,
        ]);
    }

    private function actingAsCurrent(Identity $identity): void
    {
        $currentIdentity = Identity::query()->findOrFail($identity->id);

        $this->actingAs($identity, 'web')
            ->withSession([WebSessionState::GENERATION_KEY => $currentIdentity->web_session_generation]);
    }
}
