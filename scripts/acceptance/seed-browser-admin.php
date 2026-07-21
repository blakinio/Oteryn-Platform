<?php

declare(strict_types=1);

use App\Identity\Mfa\MfaRecoveryCodes;
use App\Identity\Models\Identity;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$email = $argv[1] ?? '';
$password = $argv[2] ?? '';
$recoveryCode = $argv[3] ?? '';

if ($email === '' || $password === '' || $recoveryCode === '') {
    fwrite(STDERR, "Usage: php scripts/acceptance/seed-browser-admin.php <email> <password> <recovery-code>\n");
    exit(2);
}

$admin = Identity::query()->updateOrCreate(
    ['email' => $email],
    ['password' => Hash::make($password)],
);

$normalizer = new MfaRecoveryCodes;
$admin->forceFill([
    'web_session_generation' => 0,
    'disabled_at' => null,
    'two_factor_secret' => (new Google2FA)->generateSecretKey(),
    'two_factor_recovery_codes' => [
        Hash::make($normalizer->normalize($recoveryCode)),
    ],
    'two_factor_confirmed_at' => now(),
    'two_factor_last_used_timestep' => null,
])->save();

$platformAdminRoleId = DB::table('admin_roles')->where('key', 'platform_admin')->value('id');
if (! is_int($platformAdminRoleId) && ! (is_string($platformAdminRoleId) && ctype_digit($platformAdminRoleId))) {
    throw new RuntimeException('platform_admin role is unavailable after migrations.');
}

DB::table('identity_admin_roles')->where('identity_id', $admin->id)->delete();
DB::table('identity_admin_roles')->insert([
    'identity_id' => $admin->id,
    'role_id' => (int) $platformAdminRoleId,
]);

fwrite(STDOUT, json_encode([
    'identity_id' => $admin->id,
    'email' => $email,
], JSON_THROW_ON_ERROR)."\n");
