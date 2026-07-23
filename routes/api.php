<?php

use App\Http\Controllers\GameAuth\GameLoginTicketIssueController;
use App\Http\Middleware\GameAuth\PreventSensitiveGameAuthResponseCaching;
use Illuminate\Support\Facades\Route;

Route::post('/v1/game-auth/tickets', GameLoginTicketIssueController::class)
    ->middleware([
        PreventSensitiveGameAuthResponseCaching::class,
        'auth:api',
        'throttle:game-auth-ticket-issue',
    ]);
