<?php

declare(strict_types=1);

use App\Cms\Editorial\EditorialContentType;
use App\Cms\Editorial\EditorialPageKey;
use App\Cms\Models\EditorialTranslation;
use App\Cms\Models\ManagedPage;
use App\Identity\Mfa\MfaRecoveryCodes;
use App\Identity\Models\Identity;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (! $app->environment('acceptance')) {
    fwrite(STDERR, "Support/Legal fixture seeding is restricted to the acceptance environment.\n");
    exit(2);
}

$command = $argv[1] ?? '';

$fail = static function (string $message, int $code = 1): never {
    fwrite(STDERR, $message.PHP_EOL);
    exit($code);
};

$json = static function (array $payload): never {
    fwrite(STDOUT, json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES).PHP_EOL);
    exit(0);
};

$integerId = static function (mixed $value, string $label) use ($fail): int {
    if (is_int($value)) {
        return $value;
    }

    if (is_string($value) && ctype_digit($value)) {
        return (int) $value;
    }

    $fail("{$label} is unavailable after migrations.");
};

$reservedSlugs = EditorialPageKey::managedPageSlugs();

$reset = static function () use ($reservedSlugs): void {
    $pageIds = DB::table('managed_pages')->whereIn('slug', $reservedSlugs)->pluck('id');

    if ($pageIds->isNotEmpty()) {
        DB::table('editorial_translations')
            ->where('content_type', EditorialContentType::ManagedPage->value)
            ->whereIn('content_id', $pageIds)
            ->delete();
        DB::table('managed_page_legal_versions')
            ->whereIn('managed_page_id', $pageIds)
            ->delete();
    }

    DB::table('admin_audit_events')
        ->whereIn('action', [
            'support.content_created',
            'support.content_updated',
            'cms.translation_saved',
            'cms.translation_deleted',
        ])
        ->delete();
    DB::table('managed_pages')->whereIn('slug', $reservedSlugs)->delete();
};

if ($command === 'reset') {
    $reset();
    $json(['reset' => true]);
}

if ($command === 'seed-page') {
    $key = EditorialPageKey::tryFrom($argv[2] ?? '');
    $state = $argv[3] ?? '';
    if (! $key instanceof EditorialPageKey || ! in_array($state, ['unpublished', 'published'], true)) {
        $fail('Usage: seed-page <editorial-key> <unpublished|published>', 2);
    }

    $page = ManagedPage::query()->updateOrCreate(
        ['slug' => $key->managedPageSlug()],
        [
            'title' => 'Acceptance '.$key->label(),
            'body' => implode("\n", $key->expectedTopics())."\nAcceptance plain-text content.",
            'legal_version' => $key->isLegal() ? 'v2026.1' : null,
            'legal_effective_date' => $key->isLegal() ? '2026-07-01' : null,
            'published_at' => $state === 'published' ? now()->subMinute() : null,
        ],
    );

    $json(['id' => $page->id, 'key' => $key->value, 'state' => $state]);
}

if ($command === 'seed-public') {
    $reset();
    $now = CarbonImmutable::now('UTC')->startOfMinute();
    $pages = [];

    foreach (EditorialPageKey::cases() as $key) {
        $title = 'Acceptance '.$key->label();
        $body = implode("\n", $key->expectedTopics())."\n<img src=x onerror=alert('support')> Plain-text acceptance content.";
        $page = ManagedPage::query()->create([
            'slug' => $key->managedPageSlug(),
            'title' => $title,
            'body' => $body,
            'legal_version' => $key->isLegal() ? 'v2026.1' : null,
            'legal_effective_date' => $key->isLegal() ? '2026-07-01' : null,
            'published_at' => $now->subMinute(),
        ]);

        EditorialTranslation::query()->create([
            'content_type' => EditorialContentType::ManagedPage->value,
            'content_id' => $page->id,
            'locale' => 'pl',
            'title' => 'PL '.$key->label(),
            'body' => 'Polska treść akceptacyjna: '.implode(', ', $key->expectedTopics()),
            'source_updated_at' => $page->updated_at,
            'published_at' => $now->subMinute(),
        ]);

        $pages[$key->value] = [
            'id' => $page->id,
            'slug' => $page->slug,
            'title_en' => $title,
            'title_pl' => 'PL '.$key->label(),
        ];
    }

    $json(['pages' => $pages]);
}

if ($command === 'seed-identity') {
    $email = $argv[2] ?? '';
    $password = $argv[3] ?? '';
    $recoveryCode = $argv[4] ?? '';
    $mfaConfirmed = ($argv[5] ?? '') === 'confirmed';
    $permissions = array_values(array_filter(array_map('trim', explode(',', $argv[6] ?? ''))));

    if ($email === '' || $password === '') {
        $fail('Usage: seed-identity <email> <password> <recovery-code> <confirmed|unconfirmed> <permission-csv>', 2);
    }

    $identity = Identity::query()->updateOrCreate(
        ['email' => $email],
        ['password' => Hash::make($password)],
    );

    $attributes = [
        'web_session_generation' => 0,
        'disabled_at' => null,
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null,
        'two_factor_last_used_timestep' => null,
    ];

    if ($mfaConfirmed) {
        if ($recoveryCode === '') {
            $fail('A recovery code is required for a confirmed-MFA identity.');
        }

        $normalizer = new MfaRecoveryCodes;
        $attributes['two_factor_secret'] = (new Google2FA)->generateSecretKey();
        $attributes['two_factor_recovery_codes'] = [Hash::make($normalizer->normalize($recoveryCode))];
        $attributes['two_factor_confirmed_at'] = now();
    }

    $identity->forceFill($attributes)->save();
    DB::table('identity_admin_roles')->where('identity_id', $identity->id)->delete();

    if ($permissions !== []) {
        $roleKey = 'acceptance_support_'.$identity->id;
        $now = now();
        $roleId = DB::table('admin_roles')->where('key', $roleKey)->value('id');
        if ($roleId === null) {
            $roleId = DB::table('admin_roles')->insertGetId([
                'key' => $roleKey,
                'name' => 'Acceptance Support role',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roleId = $integerId($roleId, 'Acceptance Support role');
        DB::table('admin_role_permissions')->where('role_id', $roleId)->delete();

        foreach ($permissions as $permission) {
            $permissionId = $integerId(
                DB::table('admin_permissions')->where('key', $permission)->value('id'),
                "Permission {$permission}",
            );
            DB::table('admin_role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }

        DB::table('identity_admin_roles')->insert([
            'identity_id' => $identity->id,
            'role_id' => $roleId,
        ]);
    }

    $json([
        'identity_id' => $identity->id,
        'email' => $identity->email,
        'mfa_confirmed' => $mfaConfirmed,
        'permissions' => $permissions,
    ]);
}

$fail('Unknown Support/Legal acceptance fixture command.', 2);
