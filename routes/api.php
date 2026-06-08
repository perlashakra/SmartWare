<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Registration routes:
Route::post('/register-manager', [AuthController::class, 'registerManager']);
Route::post('/register-client', [AuthController::class, 'registerClient']);
Route::post('/register-worker', [AuthController::class, 'registerWorker']);

//Email verification routes:
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->middleware(['throttle:2,1', 'locale']);;
Route::post('/email/verification-notification',[EmailVerificationController::class, 'sendNotification'])->middleware(['auth:sanctum', 'locale']);


//Email verification page routes:
Route::post('/email/change/{id}', [AuthController::class, 'changeEmail'])->middleware('locale');
Route::post('/email/verified-login', [AuthController::class, 'verifiedLogin']);

//Password reset routes:
// 1. Send the reset link email
Route::post('/password/forgot', [AuthController::class, 'sendResetLinkEmail'])->middleware('locale');

// 2. Process the actual password reset from the link
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

//Login route:
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'locale'])->group(function () {
    Route::post('/password/change', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete', [AuthController::class, 'delete']);
});
