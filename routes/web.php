<?php

use App\Http\Controllers\Admin\AcceptBookingController;
use App\Http\Controllers\Admin\BookingMailController;
use App\Http\Controllers\Admin\BookingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FileDownloadController;
use App\Http\Controllers\Admin\FLipBookController;
use App\Http\Controllers\Admin\GoogleController;
use App\Http\Controllers\Admin\MilestoneController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ViewUserMilestoneController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\ClientDashboardController;
use App\Http\Controllers\Client\UserFlipBoardController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\FileController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;


Route::get('/s', function () {
    // session()->forget('google_token');
    // session()->forget('google_refresh_token');
    // session()->forget('pending_booking');
    dd(Session::all());
    return response()->json(['success' => true, 'message' => 'Session cleared']);
});


Route::get('/', [AuthController::class, 'welcome'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);

// Admin Routes
Route::middleware(['auth', 'isAdmin', 'isGoogleTokenValid'])->prefix('admin')->group(function () {

    Route::get('/view-flipbook', function () {

        $users = User::where('role_name', 'client')->get();

        return view('admin.flipbook.show-flipbook', compact('users'));
    });

    Route::get('/{id}/view-milestone', [ViewUserMilestoneController::class, 'index']);
    Route::resource('/milestone', MilestoneController::class);

    Route::get('/dashboard', [DashboardController::class, 'dashboard']);

    Route::get('/edit-admin', [DashboardController::class, 'show']);

    Route::put('/edit-admin-name/{id}', [DashboardController::class, 'editNameUsername']);

    Route::put('/edit-admin-password/{id}', [DashboardController::class, 'editPassword']);

    Route::resource('/clients', ClientsController::class);

    Route::resource('/bookings', BookingsController::class);

    Route::resource('/users', UserController::class);

    Route::resource('/flipbook', FLipBookController::class);

    Route::delete('/flipbook', [FLipBookController::class, 'destroy']);

    Route::resource('/files', FileController::class);

    Route::get('/download-drive-images', [FileDownloadController::class, 'downloadImage']);

    Route::put('/confirm-bookings', [AcceptBookingController::class, 'confirmBooking'])->name('confirm-bookings');
});


Route::get('/create-event', [GoogleController::class, 'createEvent']);


Route::get('auth/google/redirect', [GoogleController::class, 'redirectToGoogle'])->name('google.redirect');


Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');



//  Client Routes
Route::middleware(['auth', 'isClient', 'isGoogleTokenValid'])->prefix('client')->group(function () {

    Route::get('/view-flipbook', function () {
        return view('client.flipboard.show-flipbook');
    });

    Route::get('/dashboard', [ClientDashboardController::class, 'dashboard']);
    Route::get('/get-milestone', [ClientDashboardController::class, 'getMilestone']);
    Route::resource('/flipbook', UserFlipBoardController::class);
});


Route::get('/front-bookings', function () {
    return view('Front-Bookings.front-end-bookings');
});

Route::post('/book', [BookingMailController::class, 'sendBookMail']);

Route::post('/FormDetail', [BookingMailController::class, 'getFormDetails']);
