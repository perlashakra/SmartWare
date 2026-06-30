<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ResetPasswordController;
use Illuminate\Http\Request;
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

//Password Reset endpoints:
// Step 1: Request the 6-digit code
Route::post('/password/forgot', [ResetPasswordController::class, 'sendResetOtp']);
// Step 2: Dedicated validation endpoint for the intermediate screen
Route::post('/password/verify-otp', [ResetPasswordController::class, 'verifyOtp']);
// Step 3: Final password updating layout
Route::post('/password/reset', [ResetPasswordController::class, 'resetPasswordWithOtp']);

//Login route:
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'locale'])->group(function () {
    //Basic authentication and registration functions
    Route::post('/password/change', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete', [AuthController::class, 'delete']);
    //Onboarding functions:
    Route::get('/onboarding/getOnboardingOptions', [OnboardingController::class, 'getOnboardingOptions']);
    Route::post('/onboarding/savePreferences', [OnboardingController::class, 'savePreferences']);
});

//
