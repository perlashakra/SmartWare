<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ShipmentPlanController;
use App\Http\Controllers\WarehouseManagerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//ADMIN REQUESTS
Route::middleware(['auth', 'role:super_admin, locale'])->prefix('admin/dashboard')->group(function () {
    Route::post('/createAdmin', [AdminController::class, 'createAdmin']);
    Route::get('/requests/pending', [AdminController::class, 'pendingRequests']);
    Route::get('/requests/complete', [AdminController::class, 'completePendingRequests']);
    Route::get('/requests/{id}', [AdminController::class, 'showRequest']);
    Route::post('/requests/{id}/review', [AdminController::class, 'reviewRequest']);
});

//Registration routes:
Route::post('/register-manager', [AuthController::class, 'registerManager']);
Route::post('/register-client', [AuthController::class, 'registerClient']);
Route::post('/register-worker', [AuthController::class, 'registerWorker']);

//Email verification routes:
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->middleware(['throttle:2,1', 'locale']);;
Route::post('/email/verification-notification',[EmailVerificationController::class, 'sendNotification'])->middleware(['auth:sanctum', 'locale']);

//Email verification page routes:
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
    Route::get('/getFacilities', [OnboardingController::class, 'getAllUserFacilities']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete', [AuthController::class, 'delete']);
    //Onboarding functions:
    Route::post('/onboarding/savePreferences', [OnboardingController::class, 'savePreferences']);
    Route::post('/onboarding/uploadID', [OnboardingController::class, 'uploadIdentityDocument']);
    Route::post('/onboarding/uploadFacilityDocument', [OnboardingController::class, 'uploadFacilityDocument']);
    Route::post('/onboarding/uploadOnboardingDocuments', [OnboardingController::class, 'uploadOnboardingDocuments']);
    //Profile editing:
    Route::post('/addOrUpdatePersonalImage', [AuthController::class, 'addOrUpdatePersonalImage']);
    Route::delete('/removePersonalImage', [AuthController::class, 'removePersonalImage']);
    Route::post('/changeEmail', [AuthController::class, 'changeEmail']);
    Route::post('/changePhoneNumber', [AuthController::class, 'changePhoneNumber']);
    Route::post('/password/change', [AuthController::class, 'changePassword']);
    Route::post('/editBusinessName', [OnboardingController::class, 'editBusinessName']);

    //Order routes
    // Standard Order REST endpoints
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    // Custom workflow endpoints
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/process-decision', [OrderController::class, 'processDecision']);

    //Warehouse manager functions:
    //Worker announcement and termination
    Route::post('/warehouse_manager/announceWorker', [WarehouseManagerController::class, 'announceWorker'])->middleware(['role:warehouse_manager']);
    Route::post('/warehouse_manager/terminateJob', [WarehouseManagerController::class, 'terminateJob'])->middleware(['role:warehouse_manager']);
    Route::prefix('shipments')->group(function () {
        Route::post('/generate-plan', [ShipmentPlanController::class, 'generatePlan']);
        Route::post('/confirm-batches', [ShipmentPlanController::class, 'confirmBatches']);
    });

//Company CRUD Routes
    Route::middleware(['auth:sanctum'])->controller(CompanyController::class)->prefix('/companies')->group(function () {
        Route::get('', 'index');
        Route::get('/{company}', 'show');
        Route::post('', 'store')->middleware(['role:super_admin']);
        Route::put('/{company}', 'update')->middleware(['role:super_admin']);
        Route::delete('/{company}', 'destroy')->middleware(['role:super_admin']);
    });

//Category CRUD Routes
    Route::middleware(['auth:sanctum'])->prefix('/categories')->group(function () {
        Route::get('', [CategoryController::class, 'index']);
        Route::post('', [CategoryController::class, 'store'])->middleware(['role:super_admin,warehouse_admin']);
        Route::put('/{category}', [CategoryController::class, 'update'])->middleware(['role:super_admin,warehouse_admin']);
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->middleware(['role:super_admin']);
    });

//Product CRUD Routes
    Route::controller(ProductController::class)->prefix('/products')->middleware(['auth:sanctum', 'locale'])->group(function () {
        Route::get('', 'index');
        Route::get('/{product}', 'show');
        Route::post('', 'store')->middleware(['role:super_admin,warehouse_admin']);
        Route::put('/{product}', 'update')->middleware(['role:super_admin,warehouse_admin']);
        Route::delete('/{product}', 'destroy')->middleware(['role:super_admin']);
    });

//Product-Category Relation Routes
    Route::controller(ProductController::class)->prefix('/products')->middleware(['auth:sanctum'])->group(function () {
        Route::post('/{product}/categories', 'addCategories')->middleware(['role:super_admin,warehouse_admin']);
        Route::put('/{product}/categories', 'syncCategories')->middleware(['role:super_admin,warehouse_admin']);
        Route::delete('/{product}/categories', 'removeCategories')->middleware(['role:super_admin,warehouse_admin']);
    });

//Facility CRUD Routes
    Route::controller(FacilityController::class)->prefix('/facilities')->middleware(['auth:sanctum'])->group(function () {
        Route::get('', 'index');
        Route::get('/warehouses', 'getWarehouses');
        Route::get('/businesses', 'getBusinesses');
        Route::get('/{facility}', 'show');
        Route::post('', 'store')->middleware(['role:client,warehouse_admin']);
        Route::put('/{facility}', 'update')->middleware(['role:client,warehouse_admin']);
        Route::delete('/{facility}', 'destroy')->middleware(['role:client,warehouse_admin,super_admin']);

    });

//Importing excel files
Route::post('/import-excel', [ImportController::class, 'import'])->middleware(['auth:sanctum', 'role:warehouse_admin', 'locale']);

//Home Page
Route::controller(FacilityController::class)->prefix('/home_page')->middleware(['auth:sanctum','role:client,warehouse_admin'])->group(function(){

    Route::get('/ownedFacilities', 'getOwnedFacilities');
    Route::get('/FacilityInfo{id}', 'getFacilityInfo');
    Route::get('/sectionInfo{facility_id}{section_id}', 'getSectionInfo');
});});
