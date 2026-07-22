<?php

use App\Http\Controllers\GameAuth\RedeemGameLoginTicketController;
use Illuminate\Support\Facades\Route;

Route::post('/internal/v1/game-auth/tickets/redeem', RedeemGameLoginTicketController::class)
    ->middleware(['game-gateway.service', 'throttle:game-ticket-redeem'])
    ->name('internal.game-auth.tickets.redeem');
