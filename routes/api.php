<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProductController;
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

//Company CRUD Routes
Route::middleware(['auth:sanctum'])->controller(CompanyController::class)->prefix('/companies')->group(function (){
    Route::get('', 'index');
    Route::get('/{company}', 'show');
    Route::post('', 'store')->middleware(['role:super_admin']);
    Route::put('/{company}', 'update')->middleware(['role:super_admin']);
    Route::delete('/{company}', 'destroy')->middleware(['role:super_admin']);
});

//Category CRUD Routes
Route::middleware(['auth:sanctum'])->prefix('/categories')->group(function(){
    Route::get('', [CategoryController::class, 'index']);
    Route::post('', [CategoryController::class, 'store'])->middleware(['role:super_admin,warehouse_admin']);
    Route::put('/{category}', [CategoryController::class, 'update'])->middleware(['role:super_admin,warehouse_admin']);
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->middleware(['role:super_admin']);
});

//Product CRUD Routes
Route::controller(ProductController::class)->prefix('/products')->middleware(['auth:sanctum'])->group(function(){
    Route::get('', 'index');
    Route::get('/{product}', 'show');
    Route::post('', 'store')->middleware(['role:super_admin,warehouse_admin']);
    Route::put('/{product}', 'update')->middleware(['role:super_admin,warehouse_admin']);
    Route::delete('/{product}', 'destroy')->middleware(['role:super_admin']);
});

//Product-Category Relation Routes
Route::controller(ProductController::class)->prefix('/products')->middleware(['auth:sanctum'])->group(function(){
    Route::post('/{product}/categories', 'addCategories')->middleware(['role:super_admin,warehouse_admin']);
    Route::put('/{product}/categories', 'syncCategories')->middleware(['role:super_admin,warehouse_admin']);
    Route::delete('/{product}/categories', 'removeCategories')->middleware(['role:super_admin,warehouse_admin']);
});

//Facility CRUD Routes
Route::controller(FacilityController::class)->prefix('/facilities')->middleware(['auth:sanctum'])->group(function(){
    Route::get('', 'index');
    Route::get('/{facility}', 'show');
    Route::post('', 'store')->middleware(['role:client,warehouse_admin']);
    Route::put('/{facility}', 'update')->middleware(['role:client,warehouse_admin']);
    Route::delete('/{facility}', 'destroy')->middleware(['role:client,warehouse_admin,super_admin']);
});

//Importing excel files 
Route::post('/imports', [ImportController::class, 'import'])->middleware(['auth:sanctum']);