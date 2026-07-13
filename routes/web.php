<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth', 'is_admin'])->prefix('admin/dashboard')->group(function () {
    Route::post('/admins', [AdminController::class, 'createAdmin']);
    Route::get('/requests/pending', [AdminController::class, 'pendingRequests']);
    Route::get('/requests/{id}', [AdminController::class, 'showRequest']);
    Route::post('/requests/{id}/review', [AdminController::class, 'reviewRequest']);
});
