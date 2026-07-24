<?php

use App\Http\Middleware\EnsureConfirmedMfa;
use App\Http\Middleware\EnsureIdentitySessionIsCurrent;
use App\Http\Middleware\GameAuth\PreventSensitiveGameAuthResponseCaching;
use App\Http\Middleware\RequestCorrelation;
use App\Http\Middleware\RequireAdminPermission;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

$trustedProxies = array_values(array_filter(
    array_map(
        static fn (string $proxy): string => trim($proxy),
        explode(',', (string) env('TRUSTED_PROXIES', '')),
    ),
    static fn (string $proxy): bool => $proxy !== '',
));

if (in_array('*', $trustedProxies, true)) {
    throw new RuntimeException('TRUSTED_PROXIES must contain explicit proxy IP addresses or CIDRs; wildcard trust is not allowed.');
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/health',
        then: function (): void {
            Route::middleware('api')->group(base_path('routes/internal.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) use ($trustedProxies): void {
        if ($trustedProxies !== []) {
            $middleware->trustProxies(
                at: $trustedProxies,
                headers: Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_HOST
                    | Request::HEADER_X_FORWARDED_PORT
                    | Request::HEADER_X_FORWARDED_PROTO,
            );
        }

        $middleware->append(RequestCorrelation::class);
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/');
        $middleware->appendToGroup('web', EnsureIdentitySessionIsCurrent::class);
        $middleware->appendToGroup('web', SecurityHeaders::class);
        $middleware->alias([
            'mfa.confirmed' => EnsureConfirmedMfa::class,
            'admin.permission' => RequireAdminPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
                || $request->is('internal/*')
                || $request->expectsJson(),
        );
        $exceptions->respond(function (Response $response): Response {
            $request = request();

            return PreventSensitiveGameAuthResponseCaching::appliesTo($request)
                ? PreventSensitiveGameAuthResponseCaching::apply($response)
                : $response;
        });
    })->create();
