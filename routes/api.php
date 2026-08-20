<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ShipmentPlanController;
use App\Http\Controllers\WarehouseManagerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//ADMIN REQUESTS
Route::middleware(['auth:sanctum', 'role:super_admin', 'locale'])->prefix('admin/dashboard')->group(function () {
    Route::post('/createAdmin', [AdminController::class, 'createAdmin']);
    Route::get('/requests/pending', [AdminController::class, 'pendingRequests']);
    Route::get('/requests/complete', [AdminController::class, 'completePendingRequests']);
    Route::get('/requests/approved', [AdminController::class, 'approvedAccounts']);
    Route::get('/requests/{id}', [AdminController::class, 'showRequest']);
    Route::post('/requests/{id}/review', [AdminController::class, 'reviewRequest']);
    Route::get('/documents/{documentId}/download', [AdminController::class, 'downloadDocument'])->name('admin.documents.download');

    Route::get('/facilities/pending', [AdminController::class, 'pendingFacilities']);
    Route::get('/facilities/{id}', [AdminController::class, 'showFacilityRequest']);
    Route::post('/facilities/{id}/review', [AdminController::class, 'reviewFacility']);
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
    Route::post('/onboarding/getPreferences', [OnboardingController::class, 'getFacilityPreferences']);
    Route::post('/onboarding/submitLocation', [OnboardingController::class, 'submitLocation']);
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
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);             // GET /api/orders
        Route::post('/', [OrderController::class, 'store'])->middleware('role:client');            // POST /api/orders
        Route::post('/transfer', [OrderController::class, 'storeTransfer'])->middleware('role:warehouse_admin');
        Route::get('/{order}', [OrderController::class, 'show']);       // GET /api/orders/{id}
        Route::post('/{order}/cancel', [OrderController::class, 'cancel']); // POST /api/orders/{id}/cancel
        Route::post('/{order}/decisions', [OrderController::class, 'processDecision'])->middleware('role:warehouse_admin'); // POST /api/orders/{id}/decisions
    });

    //Warehouse manager functions:
    //Worker announcement and termination
    Route::post('/warehouse_manager/announceWorker', [WarehouseManagerController::class, 'announceWorker'])->middleware(['role:warehouse_admin']);
    Route::post('/warehouse_manager/terminateJob', [WarehouseManagerController::class, 'terminateJob'])->middleware(['role:warehouse_admin']);
    //Shipment plan generation
    Route::prefix('shipments')->group(function () {
        Route::post('/generate-plan', [ShipmentPlanController::class, 'generatePlan']);
        Route::post('/confirm-batches', [ShipmentPlanController::class, 'confirmBatches']);
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

//Discount routes
Route::controller(DiscountController::class)->prefix('/discounts')->middleware(['auth:sanctum', 'locale'])->group(function(){
    Route::get('', 'index');
    Route::get('/{discount}', 'show');
    Route::post('', 'store')->middleware(['role:warehouse_admin']);
    Route::put('/{discount}', 'update')->middleware(['role:warehouse_admin']);
    Route::delete('/{discount}', 'delete')->middleware(['role:warehouse_admin']);
});

//Inventory routes
Route::controller(InventoryController::class)->middleware(['auth:sanctum', 'locale'])->group(function(){
    Route::get('/inventories/products', 'storedProducts');
    Route::get('/inventories/products/{product}/warehouses', 'productWarehouses');
    Route::get('/inventories/{inventory}', 'show');
    Route::put('/inventories/{inventory}/adjust', 'adjust')->middleware(['role:warehouse_admin']);
    Route::get('/section/{section}/inventory', 'sectionInventory');
    Route::get('/warehouse/{warehouse}/inventory', 'warehouseInventory');
});

//Home Page
Route::controller(FacilityController::class)->prefix('/home_page')->middleware(['auth:sanctum','role:client,warehouse_admin'])->group(function(){
    Route::get('/ownedFacilities', 'getOwnedFacilities');
    Route::get('/FacilityInfo{id}', 'getFacilityInfo');
    Route::get('/showInventoryByCategory{facility_id}', 'showInventoryByCategory');
    Route::get('/sectionInfo{facility_id}{section_id}', 'getSectionInfo');
});
});

Route::controller(CartController::class)->prefix('/home_page/cart')->middleware(['auth:sanctum','role:client,warehouse_admin'])->group(function(){
    Route::get('', 'show');
    Route::post('/items', 'addItem');
    Route::post('/submit', 'submit');
    Route::put('/items/{cartItemId}', 'updateItem');
    Route::delete('/items/{cartItem}', 'removeItem');

});

Route::controller(FacilityController::class)->prefix('/worker')->middleware(['auth:sanctum','role:worker'])->group(function(){
    Route::get('{facility_id}/dashboard', 'warehouseDashboard');
    Route::post('{facility_id}/orders/{order_id}/departure', 'recordDeparture');
    Route::post('{facility_id}/orders/{order_id}/arrival', 'recordArrival');
});

Route::controller(ClientController::class)->prefix('/client')->middleware(['auth:sanctum','role:client'])->group(function(){
    Route::get('/orders', 'clientOrders');
    Route::get('/orders/{order_id}', 'clientOrder');
    Route::post('/orders/{order_id}/confirm-delivery', 'confirmDelivery');
});
