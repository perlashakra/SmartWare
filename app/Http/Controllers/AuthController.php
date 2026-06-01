<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterClientRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RegisterWorkerRequest;
use App\Models\EmployeeAnnouncement;
use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthController extends Controller
{
    public function registerManager(RegisterRequest $request)
    {
        $validatedData = $request->validated();

        $preferredLanguage =
            $request->getPreferredLanguage(['en', 'ar'])
            ?? 'en';

        $validatedData['language_preference'] = $preferredLanguage;

        /*
        |--------------------------------------------------------------------------
        | Case 3:
        | A verified user already owns this email or phone number.
        |--------------------------------------------------------------------------
        */
        $verifiedUser = User::where(function ($query) use ($validatedData) {
            $query->where('email', $validatedData['email'])
                ->orWhere('phone_number', $validatedData['phone_number']);
        })
            ->whereNotNull('email_verified_at')
            ->first();

        if ($verifiedUser) {
            return response()->json([
                'message' => __('auth.email_or_phone_already_registered'),
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Case 2:
        | User exists but has not verified their email yet.
        |--------------------------------------------------------------------------
        */

        $unverifiedUser = User::where('email', $validatedData['email'])
            ->whereNull('email_verified_at')
            ->first();

        if ($unverifiedUser) {

            $phoneOwner = User::where('phone_number', $validatedData['phone_number'])
                ->where('id', '!=', $unverifiedUser->id)
                ->first();

            if ($phoneOwner) {
                return response()->json([
                    'message' => __('auth.phone_number_already_registered'),
                ], 422);
            }

            $unverifiedUser->update([
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'phone_number' => $validatedData['phone_number'],
                'password' => Hash::make($validatedData['password']),
                'role' => $validatedData['role'],
                'language_preference' => $preferredLanguage,
            ]);

            $unverifiedUser->sendEmailVerificationNotification();

            return response()->json([
                'message' => __('auth.verification_email_resent'),
                'verification_required' => true,
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | Case 1:
        | Brand new registration.
        |--------------------------------------------------------------------------
        */

        $phoneOwner = User::where('phone_number', $validatedData['phone_number'])
            ->first();

        if ($phoneOwner) {
            return response()->json([
                'message' => __('auth.phone_number_already_registered'),
            ], 422);
        }

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        event(new Registered($user));

        return response()->json([
            'message' => __('auth.register_success'),
            'user' => $user,
            'verification_required' => true,
        ], 201);
    }

    public function registerClient(RegisterClientRequest $request)
    {
        $validatedData = $request->validated();

        $preferredLanguage =
            $request->getPreferredLanguage(['en', 'ar'])
            ?? 'en';

        $validatedData['language_preference'] = $preferredLanguage;

        /*
        |--------------------------------------------------------------------------
        | Case 3:
        | A verified user already owns this email or phone number.
        |--------------------------------------------------------------------------
        */
        $verifiedUser = User::where(function ($query) use ($validatedData) {
            $query->where('email', $validatedData['email'])
                ->orWhere('phone_number', $validatedData['phone_number']);
        })
            ->whereNotNull('email_verified_at')
            ->first();

        if ($verifiedUser) {
            return response()->json([
                'message' => __('auth.email_or_phone_already_registered'),
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Case 2:
        | User exists but has not verified their email yet.
        |--------------------------------------------------------------------------
        */

        $unverifiedUser = User::where('email', $validatedData['email'])
            ->whereNull('email_verified_at')
            ->first();

        if ($unverifiedUser) {

            $phoneOwner = User::where('phone_number', $validatedData['phone_number'])
                ->where('id', '!=', $unverifiedUser->id)
                ->first();

            if ($phoneOwner) {
                return response()->json([
                    'message' => __('auth.phone_number_already_registered'),
                ], 422);
            }

            $unverifiedUser->update([
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'phone_number' => $validatedData['phone_number'],
                'password' => Hash::make($validatedData['password']),
                'role' => $validatedData['role'],
                'language_preference' => $preferredLanguage,
            ]);

            $unverifiedUser->sendEmailVerificationNotification();

            return response()->json([
                'message' => __('auth.verification_email_resent'),
                'verification_required' => true,
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | Case 1:
        | Brand new registration.
        |--------------------------------------------------------------------------
        */

        $phoneOwner = User::where('phone_number', $validatedData['phone_number'])
            ->first();

        if ($phoneOwner) {
            return response()->json([
                'message' => __('auth.phone_number_already_registered'),
            ], 422);
        }

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'phone_number' => $validatedData['phone_number'],
            'password' => $validatedData['password'],
            'role' => $validatedData['role'],
        ]);

        Store::create([
            'name' => $validatedData['name'],
            'client_id' => $user->id,
        ]);

        event(new Registered($user));

        return response()->json([
            'message' => __('auth.register_success'),
            'user' => $user,
            'verification_required' => true,
        ], 201);
    }


    public function registerWorker(RegisterWorkerRequest $request)
    {
        $validatedData = $request->validated();
        $preferredLanguage =
            $request->getPreferredLanguage(['en', 'ar'])
            ?? 'en';
        $validatedData['language_preference'] = $preferredLanguage;

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

        /*
        |--------------------------------------------------------------------------
        | Case 3:
        | A verified user already owns this email or phone number.
        |--------------------------------------------------------------------------
        */
        $verifiedUser = User::where(function ($query) use ($validatedData) {
            $query->where('email', $validatedData['email'])
                ->orWhere('phone_number', $validatedData['phone_number']);
        })
            ->whereNotNull('email_verified_at')
            ->first();

        if ($verifiedUser) {
            return response()->json([
                'message' => __('auth.email_or_phone_already_registered'),
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Case 2:
        | User exists but has not verified their email yet.
        |--------------------------------------------------------------------------
        */

        $unverifiedUser = User::where('email', $validatedData['email'])
            ->whereNull('email_verified_at')
            ->first();

        if ($unverifiedUser) {

            $phoneOwner = User::where('phone_number', $validatedData['phone_number'])
                ->where('id', '!=', $unverifiedUser->id)
                ->first();

            if ($phoneOwner) {
                return response()->json([
                    'message' => __('auth.phone_number_already_registered'),
                ], 422);
            }

            $unverifiedUser->update([
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'phone_number' => $validatedData['phone_number'],
                'password' => Hash::make($validatedData['password']),
                'role' => $validatedData['role'],
                'language_preference' => $preferredLanguage,
            ]);

            $unverifiedUser->sendEmailVerificationNotification();

            return response()->json([
                'message' => __('auth.verification_email_resent'),
                'verification_required' => true,
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | Case 1:
        | Brand new registration.
        |--------------------------------------------------------------------------
        */

        $phoneOwner = User::where('phone_number', $validatedData['phone_number'])
            ->first();

        if ($phoneOwner) {
            return response()->json([
                'message' => __('auth.phone_number_already_registered'),
            ], 422);
        }

        $validatedData['password'] = Hash::make($validatedData['password']);

        $validatedData['warehouse_id'] = $announcement->warehouse_id;

        $validatedData['role'] = 'worker';

        $user = User::create($validatedData);

        event(new Registered($user));

        $announcement->update([
            'claimed' => true
        ]);

        $announcement->save();

        return response()->json([
            'message' => __('auth.register_success'),
            'user' => $user,
            'verification_required' => true,
        ], 201);
    }

    public function changeEmail(ChangeEmailRequest $request, $id)
    {
        $user = User::findOrFail($id);

        if(!$user)
        {
            return response()->json(['Message' => __('auth.user_not_found')],404);
        }

        $user->email = $request->email;

        // reset verification state
        $user->email_verified_at = null;

        $user->save();

        // Send new verification email
        event(new Registered($user));

        return response()->json([
            'message' => __('auth.verification_sent'),
            'email' => $user->email
        ], 200);
    }

    public function verifiedLogin(LoginRequest $request)
    {

        $user = User::where('email', $request->login)->first();

        $preferredLanguage =
            $request->getPreferredLanguage(['en', 'ar'])
            ?? 'en';

        app()->setLocale($preferredLanguage);

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => __('auth.email_not_verified')
            ], 403);
        }

        if (!Auth::attempt([
            'email' => $request->login,
            'password' => $request->password
        ])) {
            return response()->json([
                'message' => __('auth.username_password_mismatch')
            ], 401);
        }

        $user->tokens()->delete();

        $token = $user->createToken('MyApp')->plainTextToken;

        return response()->json([
            'message' => __('auth.login_success'),
            'token' => $token,
            'user' => $user,
            'role' => $user->role,
        ]);
    }
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->login)
        ->orWhere('phone_number', $request->login)
        ->first();

        $preferredLanguage =
            $request->getPreferredLanguage(['en', 'ar'])
            ?? 'en';

        app()->setLocale($preferredLanguage);

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
            'message' => __('auth.login_success'),
            'token' => $token,
            'user' => $user,
            'role' => $user->role,

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
