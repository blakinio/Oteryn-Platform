<?php

use App\Http\Middleware\EnsureConfirmedMfa;
use App\Http\Middleware\EnsureIdentitySessionIsCurrent;
use App\Http\Middleware\RequestCorrelation;
use App\Http\Middleware\RequireAdminPermission;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/health',
    )
    ->withMiddleware(function (Middleware $middleware): void {
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
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
