<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RegisterWorkerRequest;
use App\Models\EmployeeAnnouncement;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();
        $preferredLanguage =
            $request->getPreferredLanguage(['en', 'ar'])
            ?? 'en';
        $validatedData['language_preference'] = $preferredLanguage;
        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        $user->sendEmailVerificationNotification();

        event(new Registered($user));

        return response()->json([
            'Message' => __('auth.register_success'),
            'verification_required' => true,
        ], 201);
    }

    public function registerWorker(RegisterWorkerRequest $request)
    {
        $validatedData = $request->validated();

        $announcement = EmployeeAnnouncement::where(
            'national_id',
            $validatedData['national_id']
        )->first();

        if (!$announcement) {
            return response()->json([
                'message' => __('auth.employee_not_announced')
            ], 404);
        }

        if ($announcement->claimed) {
            return response()->json([
                'message' => __('auth.employee_already_registered')
            ], 409);
        }

        if (
            strtolower(trim($announcement->first_name))
            !== strtolower(trim($validatedData['first_name']))
            ||
            strtolower(trim($announcement->last_name))
            !== strtolower(trim($validatedData['last_name']))
        ) {
            return response()->json([
                'message' => __('auth.employee_identity_mismatch')
            ], 422);
        }

        $validatedData['password'] = Hash::make($validatedData['password']);

        $validatedData['warehouse_id'] = $announcement->warehouse_id;

        $user = User::create($validatedData);

        event(new Registered($user));

        $announcement->update([
            'claimed' => true
        ]);

        return response()->json([
            'message' => __('auth.register_success'),
            'verification_required' => true,
        ], 201);
    }
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->login)
            ->orWhere('phone_number', $request->login)
            ->first();

        if (!$user)
        {
            return response()->json([
                'message' => __('auth.user_not_found')
            ], 404);
        }

        $language = $user->language_preference;
        App::setLocale($language);

        $credentials = filter_var(
            $request->login,
            FILTER_VALIDATE_EMAIL
        )
            ? [
                'email' => $request->login,
                'password' => $request->password
            ]
            : [
                'phone_number' => $request->login,
                'password' => $request->password
            ];

        if (!Auth::attempt($credentials))
        {
            return response()->json([
                'message' =>
                    __('auth.username_password_mismatch')
            ], 401);
        }

        $user = Auth::user();

        //Email not verified
        if (!$user->hasVerifiedEmail()) {
            Auth::logout();

            return response()->json([
                'message' => __('auth.email_not_verified')
            ], 403);
        }

        // User has been rejected
        if ($user->account_status === 'deleted')
        {
            Auth::logout();

            return response()->json([
                'message' =>
                    __('auth.account_rejected')
            ], 403);
        }

        // Single-device login
        $user->tokens()->delete();

        $token = $user
            ->createToken('MyApp')
            ->plainTextToken;

        return response()->json([
            'message' =>
                __('auth.login_success'),

            'user' => $user,

            'role' => $user->role,

            'token' => $token,
        ], 200);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => __('auth.logout_success')
        ], 200);
    }

    public function delete(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        $user->delete();

        return response()->json([
            'message' => __('auth.delete_success')
        ], 200);
    }
}
