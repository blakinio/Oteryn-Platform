<?php

use App\Http\Controllers\GameAuth\GameLoginTicketIssueController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/game-auth/tickets', GameLoginTicketIssueController::class)
    ->middleware(['auth:api', 'throttle:game-auth-ticket-issue']);
