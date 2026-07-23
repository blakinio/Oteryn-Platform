<?php

use App\Http\Controllers\GameAuth\GameLoginContextController;
use App\Http\Controllers\GameAuth\GameLoginTicketRedeemController;
use App\Http\Middleware\GameAuth\PreventSensitiveGameAuthResponseCaching;
use App\Http\Middleware\GameAuth\RequireGatewayServiceCredential;
use Illuminate\Support\Facades\Route;

Route::post('/internal/v1/game-auth/tickets/redeem', GameLoginTicketRedeemController::class)
    ->middleware([
        PreventSensitiveGameAuthResponseCaching::class,
        'throttle:game-auth-ticket-redeem-source',
        RequireGatewayServiceCredential::class,
        'throttle:game-auth-ticket-redeem',
    ]);

Route::get('/internal/v1/game-auth/accounts/{canaryAccountId}/login-context', GameLoginContextController::class)
    ->middleware([RequireGatewayServiceCredential::class, 'throttle:game-auth-ticket-redeem']);
