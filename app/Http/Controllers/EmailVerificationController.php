<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        $preferredLanguage = $user['language_preference'];
        App::setLocale($preferredLanguage);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification())))
        {
            return response()->json([
                'message' => __('auth.invalid_verification_link')
            ], 403);
        }

        if (! $request->hasValidSignature())
        {
            return response()->json([
                'message' => __('auth.expired_verification_link')
            ], 403);
        }

        if (! $user->hasVerifiedEmail())
        {
            $user->markEmailAsVerified();
        }

        return view('auth.verified-success');
    }
    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user)
        {
            return response()->json([
                'message' => __('auth.user_not_found')
            ], 404);
        }

        App::setLocale($user->language_preference ?? 'en');

        if ($user->hasVerifiedEmail())
        {
            return response()->json(['message' => __('auth.email_already_verified')
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' =>
                __('auth.verification_sent_again')
        ]);
    }
    public function sendNotification(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' =>
                __('auth.verification_sent')
        ]);
    }
}
