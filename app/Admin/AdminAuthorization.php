<?php

namespace App\Admin;

use App\Identity\Models\Identity;
use Illuminate\Support\Facades\DB;

final class AdminAuthorization
{
    public function allows(Identity $identity, string $permission): bool
    {
        if (! in_array($permission, AdminPermission::all(), true)) {
            return false;
        }

        return DB::table('identity_admin_roles')
            ->join('admin_role_permissions', 'admin_role_permissions.role_id', '=', 'identity_admin_roles.role_id')
            ->join('admin_permissions', 'admin_permissions.id', '=', 'admin_role_permissions.permission_id')
            ->where('identity_admin_roles.identity_id', $identity->id)
            ->where('admin_permissions.key', $permission)
            ->exists();
    }
}
