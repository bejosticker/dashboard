<?php

use App\Http\Middleware\GlobalMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\authentications\LoginBasic;

Route::middleware(['global', 'web'])->group(function () {
    Route::get('/auth/login', [LoginBasic::class, 'index']);
    Route::post('/auth/login', [LoginBasic::class, 'login']);
    Route::get('/', [Analytics::class, 'index'])->name('dashboard-analytics');
});