<?php

use App\CanaryIntegration\CanaryDatabasePrivilegeVerifier;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('canary:verify-db-privileges', function () {
    try {
        $violations = app(CanaryDatabasePrivilegeVerifier::class)->inspect();
    } catch (\Throwable) {
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
