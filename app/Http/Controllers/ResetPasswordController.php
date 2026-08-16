<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Mail\ResetPasswordOtpMail;

class ResetPasswordController extends Controller
{
    /**
     * Step 1: Generate a 6-digit PIN and email it.
     */
    public function sendResetOtp(Request $request): JsonResponse
    {

        $request->validate([
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email')->whereNotNull('email_verified_at'),
            ],
        ], [
            'email.required' => __('validation.email_required'),
            'email.email' => __('validation.email_email'),
            'email.exists' => __('validation.email_not_verified'), // Add your custom error message key here
        ]);


        $user = User::where('email', $request->email)->first();

        $preferredLanguage = $user['language_preference'] ??
            $request->getPreferredLanguage(['en', 'ar']) ?? 'en';
        App::setLocale($preferredLanguage);

        // Prevent leaking whether an email exists or not for security
        if (!$user) {
            $preferredLanguage = $request->getPreferredLanguage(['en', 'ar']) ?? 'en';
            App::setLocale($preferredLanguage);

            return response()->json(['message' => __('auth.passwords_sent')], 200);
        }

        $preferredLanguage = $user['language_preference'] ??
            $request->getPreferredLanguage(['en', 'ar']) ?? 'en';
        App::setLocale($preferredLanguage);

        // Generate a random 6-digit number
        $otp = random_int(100000, 999999);

        // Store the PIN in cache bound to the email for 15 minutes
        Cache::put('password_reset_' . $request->email, $otp, now()->addMinutes(15));

        // Send the email containing just the code
        Mail::to($user->email)->locale($preferredLanguage)->send(new ResetPasswordOtpMail($otp, $user));

        return response()->json(['message' => __('auth.passwords_sent')], 200);
    }

    /**
     * Step 2: Dedicated validation endpoint for the intermediate Flutter screen.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ], [
            'email.required' => __('validation.email_required'),
            'email.email' => __('validation.email_email'),
            'otp.required' => __('validation.otp_required'),
            'otp.digits' => __('validation.otp_digits'),
        ]);

        $preferredLanguage = $user['language_preference'] ??
            $request->getPreferredLanguage(['en', 'ar']) ?? 'en';
        App::setLocale($preferredLanguage);

        $cachedOtp = Cache::get('password_reset_' . $request->email);

        if (!$cachedOtp || (int)$request->otp !== (int)$cachedOtp) {
            return response()->json([
                'message' => __('auth.passwords_token_invalid')
            ], 422);
        }

        return response()->json(['message' => __('auth.otp_verified')], 200);
    }

    /**
     * Step 3: Validate the PIN and update the password in one final action.
     */
    public function resetPasswordWithOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
            'password' => ['required', Password::min(10)],
        ], [
            'email.required' => __('validation.email_required'),
            'email.email' => __('validation.email_email'),
            'otp.required' => __('validation.otp_required'),
            'otp.digits' => __('validation.otp_digits'),
            'password.required' => __('validation.password_required'),
            'password.min' => __('validation.password_min'),
        ]);

        $cachedOtp = Cache::get('password_reset_' . $request->email);

        // Check if the OTP exists and matches what the user typed
        if (!$cachedOtp || (int)$request->otp !== (int)$cachedOtp) {
            return response()->json([
                'message' => __('auth.passwords_token_invalid')
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $preferredLanguage = $request->getPreferredLanguage(['en', 'ar']) ?? 'en';
            App::setLocale($preferredLanguage);

            return response()->json(['message' => __('auth.user_not_found')], 404);
        }

        $preferredLanguage = $user['language_preference'] ??
            $request->getPreferredLanguage(['en', 'ar']) ?? 'en';
        App::setLocale($preferredLanguage);

        // Update password securely
        $user->forceFill([
            'password' => Hash::make($request->password)
        ])->save();

        // Clear the OTP from cache so it cannot be reused
        Cache::forget('password_reset_' . $request->email);

        // Revoke Sanctum tokens forcing clean re-authentication on Flutter
        $user->tokens()->delete();

        return response()->json(['message' => __('auth.password_reset_successfully')], 200);
    }
}
