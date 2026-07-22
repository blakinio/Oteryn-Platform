<?php

use App\Http\Controllers\GameAuth\IssueGameLoginTicketController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/game-auth/tickets', IssueGameLoginTicketController::class)
    ->middleware(['auth:api', 'throttle:game-ticket-issue'])
    ->name('api.game-auth.tickets.issue');
