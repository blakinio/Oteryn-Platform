<?php

namespace App\Providers;

use App\Accounts\Contracts\CanaryAccountProvisioningGateway;
use App\CanaryIntegration\CanaryAccountProvisioner;
use App\Identity\Mfa\PendingMfaLogin;
use App\Identity\Support\CanonicalEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CanaryAccountProvisioningGateway::class, CanaryAccountProvisioner::class);
    }

    public function boot(): void
    {
        RateLimiter::for('identity-registration', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip() ?? 'unknown');
        });

        RateLimiter::for('identity-login', function (Request $request): Limit {
            return Limit::perMinute(5)->by($this->emailSourceKey($request));
        });

        RateLimiter::for('identity-login-source', function (Request $request): Limit {
            return Limit::perMinute(20)->by($request->ip() ?? 'unknown');
        });

        RateLimiter::for('identity-password-recovery', function (Request $request): Limit {
            return Limit::perMinute(3)->by($this->emailSourceKey($request));
        });

        RateLimiter::for('identity-password-recovery-source', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip() ?? 'unknown');
        });

        RateLimiter::for('identity-password-reset', function (Request $request): Limit {
            return Limit::perMinute(5)->by($this->emailSourceKey($request));
        });

        RateLimiter::for('identity-password-change', function (Request $request): Limit {
            return Limit::perMinute(5)->by($this->authenticatedIdentitySourceKey($request));
        });

        RateLimiter::for('identity-mfa-challenge', function (Request $request): Limit {
            $sourceIp = $request->ip() ?? 'unknown';

            return Limit::perMinute(5)->by($this->pendingMfaIdentityKey($request).'|'.$sourceIp);
        });

        RateLimiter::for('identity-mfa-challenge-identity', function (Request $request): Limit {
            return Limit::perMinute(10)->by($this->pendingMfaIdentityKey($request));
        });

        RateLimiter::for('identity-mfa-challenge-source', function (Request $request): Limit {
            return Limit::perMinute(20)->by($request->ip() ?? 'unknown');
        });

        RateLimiter::for('identity-mfa-enrollment', function (Request $request): Limit {
            return Limit::perMinute(5)->by($this->authenticatedIdentitySourceKey($request));
        });

        RateLimiter::for('identity-mfa-disable', function (Request $request): Limit {
            return Limit::perMinute(5)->by($this->authenticatedIdentitySourceKey($request));
        });
    }

    private function emailSourceKey(Request $request): string
    {
        $email = $request->input('email');
        $canonicalEmail = is_string($email) ? CanonicalEmail::normalize($email) : '';
        $identityKey = hash('sha256', $canonicalEmail);
        $sourceIp = $request->ip() ?? 'unknown';

        return $identityKey.'|'.$sourceIp;
    }

    private function authenticatedIdentitySourceKey(Request $request): string
    {
        $identifier = $request->user()?->getAuthIdentifier();
        $identityKey = is_int($identifier) || is_string($identifier)
            ? hash('sha256', (string) $identifier)
            : 'unknown';
        $sourceIp = $request->ip() ?? 'unknown';

        return $identityKey.'|'.$sourceIp;
    }

    private function pendingMfaIdentityKey(Request $request): string
    {
        $pendingIdentityId = $request->session()->get(PendingMfaLogin::IDENTITY_ID_KEY);

        return is_int($pendingIdentityId) || is_string($pendingIdentityId)
            ? hash('sha256', (string) $pendingIdentityId)
            : 'unknown';
    }
}
