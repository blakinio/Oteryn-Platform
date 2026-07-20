<?php

use App\Accounts\Actions\ProvisionCanaryAccount;
use App\Accounts\Exceptions\CanaryAccountProvisioningException;
use App\Accounts\Models\IdentityCanaryAccount;
use App\Admin\AdminRoleManager;
use App\CanaryIntegration\CanaryCharacterCreateDatabasePrivilegeVerifier;
use App\CanaryIntegration\CanaryDatabasePrivilegeVerifier;
use App\CanaryIntegration\CanaryProvisioningDatabasePrivilegeVerifier;
use App\Identity\Models\Identity;
use App\Identity\Support\CanonicalEmail;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('canary:verify-db-privileges', function () {
    try {
        $violations = app(CanaryDatabasePrivilegeVerifier::class)->inspect();
    } catch (Throwable) {
        $this->error('Unable to inspect the Canary database privilege boundary.');

        return 1;
    }

    if ($violations !== []) {
        $this->error('Canary database privilege boundary verification failed.');

        foreach ($violations as $violation) {
            $this->line("- {$violation}");
        }

        return 1;
    }

    $this->info('Canary database privilege boundary verified: direct SELECT only on the approved table allowlist.');

    return 0;
})->purpose('Verify that the Canary database credential is restricted to the approved SELECT-only table allowlist');

Artisan::command('canary:verify-provisioning-db-privileges', function () {
    try {
        $violations = app(CanaryProvisioningDatabasePrivilegeVerifier::class)->inspect();
    } catch (Throwable) {
        $this->error('Unable to inspect the Canary provisioning database privilege boundary.');

        return 1;
    }

    if ($violations !== []) {
        $this->error('Canary provisioning database privilege boundary verification failed.');

        foreach ($violations as $violation) {
            $this->line("- {$violation}");
        }

        return 1;
    }

    $this->info('Canary provisioning database privilege boundary verified: approved column-level accounts INSERT/SELECT only.');

    return 0;
})->purpose('Verify the dedicated Canary account-provisioning credential least-privilege boundary');

Artisan::command('canary:verify-character-create-db-privileges', function () {
    try {
        $violations = app(CanaryCharacterCreateDatabasePrivilegeVerifier::class)->inspect();
    } catch (Throwable) {
        $this->error('Unable to inspect the Canary character-create database privilege boundary.');

        return 1;
    }

    if ($violations !== []) {
        $this->error('Canary character-create database privilege boundary verification failed.');

        foreach ($violations as $violation) {
            $this->line("- {$violation}");
        }

        return 1;
    }

    $this->info('Canary character-create database privilege boundary verified: approved column-level account/player SELECT and player INSERT only.');

    return 0;
})->purpose('Verify the dedicated Canary character-create credential least-privilege boundary');

Artisan::command('canary:provision-pending-accounts {--limit=100}', function () {
    $limitOption = $this->option('limit');
    $limit = is_int($limitOption) || is_string($limitOption)
        ? filter_var($limitOption, FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => 1,
                'max_range' => 1000,
            ],
        ])
        : false;

    if ($limit === false) {
        $this->error('The --limit option must be an integer between 1 and 1000.');

        return 1;
    }

    $pendingBindings = IdentityCanaryAccount::query()
        ->where('status', IdentityCanaryAccount::STATUS_PENDING)
        ->orderBy('identity_id')
        ->limit($limit)
        ->get();

    $completed = 0;
    $failed = 0;
    $provision = app(ProvisionCanaryAccount::class);

    foreach ($pendingBindings as $pendingBinding) {
        try {
            $binding = $provision->execute($pendingBinding->identity_id);

            if ($binding->isReady()) {
                $completed++;
            }
        } catch (CanaryAccountProvisioningException) {
            $failed++;
        }
    }

    $this->info("Processed {$pendingBindings->count()} pending account provisioning record(s): {$completed} ready, {$failed} failed.");

    return $failed === 0 ? 0 : 1;
})->purpose('Retry bounded pending Platform-originated Canary account provisioning records');

Artisan::command('admin:bootstrap {email}', function () {
    $emailArgument = $this->argument('email');

    if (! is_string($emailArgument)) {
        $this->error('The email argument must be a string.');

        return 1;
    }

    $canonicalEmail = CanonicalEmail::normalize($emailArgument);
    $identity = Identity::query()->where('email', $canonicalEmail)->first();

    if (! $identity instanceof Identity) {
        $this->error('No Platform Identity exists for the supplied email.');

        return 1;
    }

    try {
        app(AdminRoleManager::class)->bootstrapFirstPlatformAdmin($identity->id);
    } catch (\DomainException|\InvalidArgumentException $exception) {
        $this->error($exception->getMessage());

        return 1;
    }

    $this->info("First platform administrator assigned to {$identity->email}.");

    return 0;
})->purpose('Assign the one-time first platform_admin role to an MFA-confirmed Platform Identity');
