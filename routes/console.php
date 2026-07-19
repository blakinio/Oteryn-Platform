<?php

use App\Accounts\Actions\ProvisionCanaryAccount;
use App\Accounts\Exceptions\CanaryAccountProvisioningException;
use App\Accounts\Models\IdentityCanaryAccount;
use App\CanaryIntegration\CanaryDatabasePrivilegeVerifier;
use App\CanaryIntegration\CanaryProvisioningDatabasePrivilegeVerifier;
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
