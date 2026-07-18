<?php

use App\Http\Controllers\PublicGameData\PublicGameDataController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/highscores', [PublicGameDataController::class, 'highscores'])->name('game.highscores.index');
Route::get('/characters/{name}', [PublicGameDataController::class, 'character'])->name('game.characters.show');
Route::get('/guilds/{name}', [PublicGameDataController::class, 'guild'])->name('game.guilds.show');
Route::get('/servers', [PublicGameDataController::class, 'servers'])->name('game.servers.index');
