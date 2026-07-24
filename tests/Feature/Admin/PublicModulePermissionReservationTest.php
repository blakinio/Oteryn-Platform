<?php

namespace Tests\Feature\Admin;

use App\Admin\AdminPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PublicModulePermissionReservationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var list<string>
     */
    private const RESERVED_KEYS = [
        'portal.access',
        'portal.announcements.manage',
        'portal.settings.manage',
        'downloads.manage',
        'events.manage',
        'events.publish',
        'support.content.manage',
        'wiki.access',
        'wiki.articles.manage',
        'wiki.categories.manage',
        'wiki.publish',
    ];

    public function test_public_module_permissions_are_registered_without_implicit_role_grants(): void
    {
        foreach (self::RESERVED_KEYS as $permissionKey) {
            $this->assertDatabaseHas('admin_permissions', ['key' => $permissionKey]);
            self::assertContains($permissionKey, AdminPermission::all());
        }

        $grantedCount = DB::table('admin_role_permissions')
            ->join('admin_permissions', 'admin_permissions.id', '=', 'admin_role_permissions.permission_id')
            ->whereIn('admin_permissions.key', self::RESERVED_KEYS)
            ->count();

        self::assertSame(0, $grantedCount);
    }
}
