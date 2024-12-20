<?php

use App\Http\Controllers\Admin\BookingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\ClientsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'welcome'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);


// Admin Routes
Route::middleware(['auth', 'isAdmin'])->prefix('admin')->group(function () {


    Route::get('/dashboard', [DashboardController::class, 'dashboard']);
    Route::get('/edit-admin', [DashboardController::class, 'show']);
    Route::put('/edit-admin-name/{id}', [DashboardController::class, 'editNameUsername']);
    Route::put('/edit-admin-password/{id}', [DashboardController::class, 'editPassword']);

    Route::resource('/clients', ClientsController::class);

    Route::resource('/bookings', BookingsController::class);

    Route::resource('/users', UserController::class);
    
});


//  Client Routes
Route::middleware(['auth', 'isClient'])->prefix('client')->group(function () {

    Route::get('/dashboard', [ClientDashboardController::class, 'dashboard']);
});
