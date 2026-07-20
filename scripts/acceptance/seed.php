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

$command = $argv[1] ?? 'seed';

if ($command === 'empty-news') {
    DB::table('news_posts')->delete();
    fwrite(STDOUT, "acceptance-state: news emptied\n");
    exit(0);
}

if ($command !== 'seed') {
    fwrite(STDERR, "Unknown acceptance seed command: {$command}\n");
    exit(2);
}

$now = now();

DB::table('news_posts')->delete();
DB::table('news_posts')->insert([
    [
        'slug' => 'welcome-to-oteryn',
        'title' => 'Welcome to the Oteryn Platform acceptance environment',
        'body' => "This is deterministic production-like acceptance content.\n\nIt intentionally contains multiple paragraphs so typography, spacing, wrapping, and long-form readability can be evaluated without using production data.",
        'published_at' => $now->copy()->subDay(),
        'created_at' => $now,
        'updated_at' => $now,
    ],
    [
        'slug' => 'long-title-layout-probe',
        'title' => 'A deliberately long published news title used to verify wrapping and visual hierarchy across narrow mobile viewports',
        'body' => str_repeat('Long-form acceptance content verifies that plain-text CMS output wraps safely and does not break the page layout. ', 8),
        'published_at' => $now->copy()->subHours(2),
        'created_at' => $now,
        'updated_at' => $now,
    ],
]);

DB::table('managed_pages')->delete();
DB::table('managed_pages')->insert([
    'slug' => 'about-oteryn',
    'title' => 'About Oteryn',
    'body' => "Managed public page acceptance content.\n\nThis page is published and intentionally plain text so its real production rendering can be evaluated.",
    'published_at' => $now->copy()->subHour(),
    'created_at' => $now,
    'updated_at' => $now,
]);

$regular = Identity::query()->updateOrCreate(
    ['email' => 'visual.user@example.test'],
    ['password' => Hash::make('Acceptance-User-9!Pass')],
);
$regular->forceFill([
    'web_session_generation' => 0,
    'disabled_at' => null,
    'two_factor_secret' => null,
    'two_factor_recovery_codes' => null,
    'two_factor_confirmed_at' => null,
    'two_factor_last_used_timestep' => null,
])->save();

$admin = Identity::query()->updateOrCreate(
    ['email' => 'visual.admin@example.test'],
    ['password' => Hash::make('Acceptance-Admin-9!Pass')],
);

$recoveryCodes = [
    'ADMIN-00001',
    'ADMIN-00002',
    'ADMIN-00003',
    'ADMIN-00004',
];
$normalizer = new MfaRecoveryCodes;
$recoveryHashes = array_map(
    static fn (string $code): string => Hash::make($normalizer->normalize($code)),
    $recoveryCodes,
);

$admin->forceFill([
    'web_session_generation' => 0,
    'disabled_at' => null,
    'two_factor_secret' => (new Google2FA)->generateSecretKey(),
    'two_factor_recovery_codes' => $recoveryHashes,
    'two_factor_confirmed_at' => $now,
    'two_factor_last_used_timestep' => null,
])->save();

$observer = Identity::query()->updateOrCreate(
    ['email' => 'extremely.long.identity.address.for.mobile.table.layout.acceptance@example.test'],
    ['password' => Hash::make('Acceptance-Observer-9!Pass')],
);
$observer->forceFill([
    'web_session_generation' => 0,
    'disabled_at' => null,
    'two_factor_secret' => null,
    'two_factor_recovery_codes' => null,
    'two_factor_confirmed_at' => null,
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

DB::table('admin_audit_events')->delete();
DB::table('admin_audit_events')->insert([
    [
        'actor_identity_id' => $admin->id,
        'action' => 'cms.news.saved',
        'target_type' => 'news_post',
        'target_id' => '1',
        'metadata' => json_encode(['slug' => 'welcome-to-oteryn'], JSON_THROW_ON_ERROR),
        'occurred_at' => $now->copy()->subMinutes(20),
    ],
    [
        'actor_identity_id' => $admin->id,
        'action' => 'admin.role_assigned',
        'target_type' => 'identity',
        'target_id' => (string) $observer->id,
        'metadata' => json_encode([
            'role' => 'content_editor',
            'acceptance_probe' => 'Long metadata value used to verify table wrapping rather than leaking production audit content.',
        ], JSON_THROW_ON_ERROR),
        'occurred_at' => $now->copy()->subMinutes(10),
    ],
]);

fwrite(STDOUT, json_encode([
    'regular_identity_id' => $regular->id,
    'admin_identity_id' => $admin->id,
    'observer_identity_id' => $observer->id,
    'admin_recovery_codes' => $recoveryCodes,
], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)."\n");
