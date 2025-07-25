<?php

use App\Http\Controllers\CetakProductController;
use App\Http\Controllers\CetakSalesController;
use App\Http\Controllers\OnlineAdController;
use App\Http\Controllers\OnlineIncomeController;
use App\Http\Controllers\PengambilanBahanController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\TokoIncomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketOnlineController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GajiController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KulakController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MetodePembayaranController;

Route::middleware(['global', 'web'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/login', [AuthController::class, 'index']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/logout', [AuthController::class, 'logout']);
    });
    Route::get('/', [HomeController::class, 'index'])->name('beranda');

    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('suppliers');
        Route::post('/', [SupplierController::class, 'store']);
        Route::post('/update/{id}', [SupplierController::class, 'update']);
        Route::get('/delete/{id}', [SupplierController::class, 'destroy']);
    });

    Route::prefix('metode-pembayaran')->group(function () {
        Route::get('/', [MetodePembayaranController::class, 'index'])->name('metode-pembayaran');
        Route::post('/', [MetodePembayaranController::class, 'store']);
        Route::post('/update/{id}', [MetodePembayaranController::class, 'update']);
        Route::get('/delete/{id}', [MetodePembayaranController::class, 'destroy']);
    });

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('products');
        Route::post('/', [ProductController::class, 'store']);
        Route::post('/update/{id}', [ProductController::class, 'update']);
        Route::get('/delete/{id}', [ProductController::class, 'destroy']);
    });

    Route::prefix('cetak-products')->group(function () {
        Route::get('/', [CetakProductController::class, 'index'])->name('cetak-products');
        Route::post('/', [CetakProductController::class, 'store']);
        Route::post('/update/{id}', [CetakProductController::class, 'update']);
        Route::get('/delete/{id}', [CetakProductController::class, 'destroy']);
    });

    Route::prefix('toko')->group(function () {
        Route::get('/', [TokoController::class, 'index'])->name('toko');
        Route::post('/', [TokoController::class, 'store']);
        Route::post('/update/{id}', [TokoController::class, 'update']);
        Route::get('/delete/{id}', [TokoController::class, 'destroy']);
    });

    Route::prefix('online-toko')->group(function () {
        Route::get('/', [MarketOnlineController::class, 'index'])->name('online-toko');
        Route::post('/', [MarketOnlineController::class, 'store']);
        Route::post('/update/{id}', [MarketOnlineController::class, 'update']);
        Route::get('/delete/{id}', [MarketOnlineController::class, 'destroy']);
    });

    Route::prefix('karyawan')->group(function () {
        Route::get('/', [KaryawanController::class, 'index'])->name('karyawan');
        Route::post('/', [KaryawanController::class, 'store']);
        Route::post('/update/{id}', [KaryawanController::class, 'update']);
        Route::get('/delete/{id}', [KaryawanController::class, 'destroy']);
    });

    Route::prefix('gaji-history')->group(function () {
        Route::get('/', [GajiController::class, 'history'])->name('gaji-history');
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users');
        Route::post('/', [UserController::class, 'store']);
        Route::post('/update/{id}', [UserController::class, 'update']);
        Route::post('/update-password/{id}', [UserController::class, 'updatePassword']);
        Route::get('/delete/{id}', [UserController::class, 'destroy']);
    });

    Route::prefix('sales')->group(function () {
        Route::get('/', [SalesController::class, 'index'])->name('sales');
        Route::get('/delete/{id}', [SalesController::class, 'destroy']);
    });

    Route::prefix('cetak-sales')->group(function () {
        Route::get('/', [CetakSalesController::class, 'index'])->name('cetak-sales');
        Route::get('/delete/{id}', [CetakSalesController::class, 'destroy']);
    });

    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports');
    });

    Route::prefix('toko-reports')->group(function () {
        Route::get('/', [ReportController::class, 'tokoReport'])->name('toko-reports');
    });

    Route::prefix('online-reports')->group(function () {
        Route::get('/', [ReportController::class, 'onlineReport'])->name('online-reports');
    });

    Route::prefix('kulak')->group(function () {
        Route::get('/', [KulakController::class, 'index'])->name('kulak');
        Route::get('/delete/{id}', [KulakController::class, 'destroy']);
    });

    Route::prefix('pengambilan-bahan')->group(function () {
        Route::get('/', [PengambilanBahanController::class, 'index'])->name('pengambilan-bahan');
        Route::post('/', [PengambilanBahanController::class, 'store']);
        Route::get('/delete/{id}', [PengambilanBahanController::class, 'destroy']);
    });

    Route::prefix('toko-income')->group(function () {
        Route::get('/', [TokoIncomeController::class, 'index'])->name('toko-income');
        Route::post('/', [TokoIncomeController::class, 'store']);
        Route::post('/update/{id}', [TokoIncomeController::class, 'update']);
        Route::get('/delete/{id}', [TokoIncomeController::class, 'destroy']);
    });

    Route::prefix('online-incomes')->group(function () {
        Route::get('/', [OnlineIncomeController::class, 'index'])->name('online-incomes');
        Route::post('/', [OnlineIncomeController::class, 'store']);
        Route::post('/update/{id}', [OnlineIncomeController::class, 'update']);
        Route::get('/delete/{id}', [OnlineIncomeController::class, 'destroy']);
    });

    Route::prefix('online-ads')->group(function () {
        Route::get('/', [OnlineAdController::class, 'index'])->name('online-ads');
        Route::post('/', [OnlineAdController::class, 'store']);
        Route::post('/update/{id}', [OnlineAdController::class, 'update']);
        Route::get('/delete/{id}', [OnlineAdController::class, 'destroy']);
    });

    Route::prefix('gaji')->group(function () {
        Route::get('/', [GajiController::class, 'index'])->name('gaji');
        Route::post('/', [GajiController::class, 'store']);
        Route::get('/detail/{id}', [GajiController::class, 'detail']);
        Route::get('/delete/{id}', [GajiController::class, 'destroy']);
    });

    Route::prefix('pengeluaran')->group(function () {
        Route::get('/', [PengeluaranController::class, 'index'])->name('pengeluaran');
        Route::post('/', [PengeluaranController::class, 'store']);
        Route::post('/update/{id}', [PengeluaranController::class, 'update']);
        Route::get('/delete/{id}', [PengeluaranController::class, 'destroy']);
    });
});