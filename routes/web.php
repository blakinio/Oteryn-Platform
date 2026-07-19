<?php

use App\Http\Controllers\Identity\RegistrationController;
use App\Http\Controllers\Identity\SessionController;
use App\Http\Controllers\PublicGameData\PublicGameDataController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/register', [RegistrationController::class, 'create'])
    ->middleware('guest')
    ->name('identity.register.create');
Route::post('/register', [RegistrationController::class, 'store'])
    ->middleware(['guest', 'throttle:identity-registration'])
    ->name('identity.register.store');

Route::get('/login', [SessionController::class, 'create'])
    ->middleware('guest')
    ->name('identity.login.create');
Route::post('/login', [SessionController::class, 'store'])
    ->middleware(['guest', 'throttle:identity-login', 'throttle:identity-login-source'])
    ->name('identity.login.store');
Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('identity.logout');

Route::get('/highscores', [PublicGameDataController::class, 'highscores'])->name('game.highscores.index');
Route::get('/characters/{name}', [PublicGameDataController::class, 'character'])->name('game.characters.show');
Route::get('/guilds/{name}', [PublicGameDataController::class, 'guild'])->name('game.guilds.show');
Route::get('/servers', [PublicGameDataController::class, 'servers'])->name('game.servers.index');
