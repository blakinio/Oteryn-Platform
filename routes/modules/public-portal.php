<?php

use App\Http\Controllers\PublicPortal\PublicHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('home');
Route::view('/design/home-v2', 'home-preview')->name('design.home.v2');
