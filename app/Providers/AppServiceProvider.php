<?php

namespace App\Providers;

use App\Identity\Support\CanonicalEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('identity-registration', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip() ?? 'unknown');
        });

        RateLimiter::for('identity-login', function (Request $request): Limit {
            $email = $request->input('email');
            $canonicalEmail = is_string($email) ? CanonicalEmail::normalize($email) : '';
            $identityKey = hash('sha256', $canonicalEmail);
            $sourceIp = $request->ip() ?? 'unknown';

            return Limit::perMinute(5)->by($identityKey.'|'.$sourceIp);
        });

        RateLimiter::for('identity-login-source', function (Request $request): Limit {
            return Limit::perMinute(20)->by($request->ip() ?? 'unknown');
        });
    }
}
