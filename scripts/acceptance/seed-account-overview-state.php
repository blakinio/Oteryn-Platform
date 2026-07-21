<?php

declare(strict_types=1);

use App\Accounts\Actions\ProvisionCanaryAccount;
use App\Accounts\Models\IdentityCanaryAccount;
use App\Identity\Models\Identity;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (! $app->environment('acceptance')) {
    fwrite(STDERR, "Account overview fixture seeding is restricted to the acceptance environment.\n");
    exit(2);
}

$email = $argv[1] ?? '';
$password = $argv[2] ?? '';
$state = $argv[3] ?? '';

if ($email === '' || $password === '' || ! in_array($state, ['ready', 'pending', 'recoverable', 'conflict', 'missing'], true)) {
    fwrite(STDERR, "Usage: php scripts/acceptance/seed-account-overview-state.php <email> <password> <ready|pending|recoverable|conflict|missing>\n");
    exit(2);
}

$identity = Identity::query()->updateOrCreate(
    ['email' => $email],
    ['password' => Hash::make($password)],
);
$identity->forceFill([
    'web_session_generation' => 0,
    'disabled_at' => null,
    'two_factor_secret' => null,
    'two_factor_recovery_codes' => null,
    'two_factor_confirmed_at' => null,
    'two_factor_last_used_timestep' => null,
])->save();

if ($state === 'missing') {
    IdentityCanaryAccount::query()->whereKey($identity->id)->delete();

    fwrite(STDOUT, json_encode([
        'email' => $identity->email,
        'state' => $state,
    ], JSON_THROW_ON_ERROR)."\n");
    exit(0);
}

$provisioningName = 'op'.substr(hash('sha256', 'account-overview-'.$email), 0, 30);
$creationEpoch = 2_000_100_000 + $identity->id;
$accountId = 3_000_000_000 + $identity->id;

$attributes = [
    'canary_account_id' => null,
    'provisioning_name' => $provisioningName,
    'canary_creation_epoch' => $creationEpoch,
    'status' => IdentityCanaryAccount::STATUS_PENDING,
    'last_failure_code' => null,
    'last_attempt_at' => now()->subMinute(),
    'ready_at' => null,
];

if ($state === 'ready') {
    $attributes['canary_account_id'] = $accountId;
    $attributes['status'] = IdentityCanaryAccount::STATUS_READY;
    $attributes['ready_at'] = now()->subMinute();
} elseif ($state === 'recoverable') {
    $attributes['last_failure_code'] = ProvisionCanaryAccount::FAILURE_DEPENDENCY_UNAVAILABLE;
} elseif ($state === 'conflict') {
    $attributes['status'] = IdentityCanaryAccount::STATUS_CONFLICT;
    $attributes['last_failure_code'] = ProvisionCanaryAccount::FAILURE_BINDING_CONFLICT;
}

IdentityCanaryAccount::query()->updateOrCreate(
    ['identity_id' => $identity->id],
    $attributes,
);

fwrite(STDOUT, json_encode([
    'email' => $identity->email,
    'state' => $state,
    'canary_account_id' => $state === 'ready' ? $accountId : null,
    'provisioning_name' => $provisioningName,
], JSON_THROW_ON_ERROR)."\n");
