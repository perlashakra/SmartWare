<?php

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/email/verify/{id}/{hash}', function (
    Request $request, $id, $hash)
{
    $user = User::findOrFail($id);

    $preferredLanguage = $user['language_preference'];
    App::setLocale($preferredLanguage);

    if (! hash_equals(
        (string) $hash,
        sha1($user->getEmailForVerification())
    )) {
        return response()->json([
            'message' => __('auth.invalid_verification_link')
        ], 403);
    }

    if (! $request->hasValidSignature()) {
        return response()->json([
            'message' => __('auth.expired_verification_link')
        ], 403);
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return response()->json([
        'message' => __('auth.email_verified')
    ]);
})->middleware(['signed'])->name('verification.verify');

Route::post('/email/resend', function (Request $request)
{
    $request->validate(['email' => 'required|email']);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'message' => __('auth.user_not_found')
        ], 404);
    }

    App::setLocale(
        $user->language_preference ?? 'en'
    );

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => __('auth.email_already_verified')
        ], 400);
    }

    $user->sendEmailVerificationNotification();

    return response()->json([
        'message' =>
            __('auth.verification_sent_again')
    ]);
})->middleware(['throttle:2,1', 'locale']);;

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

//Email verification page routes
Route::post('/email/change/{id}', [AuthController::class, 'changeEmail'])->middleware('locale');
Route::post('/email/verified-login', [AuthController::class, 'verifiedLogin'])->middleware('locale');

Route::post('/login', [AuthController::class, 'login'])->middleware('locale');

Route::middleware(['auth:sanctum', 'locale', 'verified'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete', [AuthController::class, 'delete']);
});
