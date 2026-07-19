<?php

use App\Http\Controllers\Identity\Mfa\MfaChallengeController;
use App\Http\Controllers\Identity\Mfa\MfaEnrollmentController;
use App\Http\Controllers\Identity\PasswordChangeController;
use App\Http\Controllers\Identity\PasswordRecoveryController;
use App\Http\Controllers\Identity\PasswordResetController;
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

Route::get('/mfa/challenge', [MfaChallengeController::class, 'create'])
    ->middleware('guest')
    ->name('identity.mfa.challenge.create');
Route::post('/mfa/challenge', [MfaChallengeController::class, 'store'])
    ->middleware(['guest', 'throttle:identity-mfa-challenge', 'throttle:identity-mfa-challenge-source'])
    ->name('identity.mfa.challenge.store');

Route::get('/mfa', [MfaEnrollmentController::class, 'show'])
    ->middleware('auth')
    ->name('identity.mfa.settings');
Route::post('/mfa/enroll', [MfaEnrollmentController::class, 'store'])
    ->middleware(['auth', 'throttle:identity-mfa-enrollment'])
    ->name('identity.mfa.enroll');
Route::post('/mfa/confirm', [MfaEnrollmentController::class, 'confirm'])
    ->middleware(['auth', 'throttle:identity-mfa-enrollment'])
    ->name('identity.mfa.confirm');
Route::delete('/mfa', [MfaEnrollmentController::class, 'destroy'])
    ->middleware(['auth', 'throttle:identity-mfa-disable'])
    ->name('identity.mfa.destroy');

Route::get('/forgot-password', [PasswordRecoveryController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');
Route::post('/forgot-password', [PasswordRecoveryController::class, 'store'])
    ->middleware(['guest', 'throttle:identity-password-recovery', 'throttle:identity-password-recovery-source'])
    ->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'store'])
    ->middleware(['guest', 'throttle:identity-password-reset'])
    ->name('password.update');

Route::get('/password/change', [PasswordChangeController::class, 'create'])
    ->middleware('auth')
    ->name('identity.password.change.create');
Route::put('/password/change', [PasswordChangeController::class, 'update'])
    ->middleware(['auth', 'throttle:identity-password-change'])
    ->name('identity.password.change.update');

Route::get('/highscores', [PublicGameDataController::class, 'highscores'])->name('game.highscores.index');
Route::get('/characters/{name}', [PublicGameDataController::class, 'character'])->name('game.characters.show');
Route::get('/guilds/{name}', [PublicGameDataController::class, 'guild'])->name('game.guilds.show');
Route::get('/servers', [PublicGameDataController::class, 'servers'])->name('game.servers.index');
