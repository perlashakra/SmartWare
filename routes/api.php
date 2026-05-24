<?php

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/email/verify/{id}/{hash}', function (
    Request $request, $id, $hash)
{
    $user = User::findOrFail($id);

    if (! hash_equals(
        (string) $hash,
        sha1($user->getEmailForVerification())
    )) {
        abort(403);
    }

    if (! $request->hasValidSignature()) {
        abort(403);
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return response()->json([
        'message' => __('auth.email_verified')
    ]);
})->middleware(['signed', 'locale'])->name('verification.verify');

Route::post('/email/verification-notification',
    function (Request $request) {

        $request->user()
            ->sendEmailVerificationNotification();

        return response()->json([
            'message' =>
                __('auth.verification_sent')
        ]);
    })->middleware(['auth:sanctum', 'locale']);

Route::post('/register', [AuthController::class, 'register'])->middleware('locale');
Route::post('/registerWorker', [AuthController::class, 'registerWorker'])->middleware('locale');

Route::post('/login', [AuthController::class, 'login'])->middleware(['locale', 'verified']);

Route::middleware(['auth:sanctum', 'locale', 'verified'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete', [AuthController::class, 'delete']);
});
