<?php

use App\Http\Middleware\GlobalMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;

Route::middleware(['global', 'web'])->group(function () {
    Route::get('/auth/login', [AuthController::class, 'index']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/', [HomeController::class, 'index'])->name('beranda');
});