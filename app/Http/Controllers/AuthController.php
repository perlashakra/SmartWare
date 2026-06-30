<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterClientRequest;
use App\Http\Requests\RegisterManagerRequest;
use App\Http\Requests\RegisterWorkerRequest;
use App\Models\EmployeeAnnouncement;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Password as PasswordBroker;

class AuthController extends Controller
{
    /**
     * Core Registration Engine handling Cases 1, 2, and 3 consistently.
     */
    private function processRegistration(array $data, string $preferredLanguage, ?callable $afterSaveCallback = null): JsonResponse
    {
        $email = $data['email'];
        $phone = $data['phone_number'];

        // --- CASE 3: Pre-emptively block if ANY verified user owns this email or phone ---
        $verifiedUser = User::where(function ($query) use ($email, $phone) {
            $query->where('email', $email)->orWhere('phone_number', $phone);
        })
            ->whereNotNull('email_verified_at')
            ->first();

        if ($verifiedUser) {
            return response()->json(['message' => __('auth.email_or_phone_already_registered')], 422);
        }

        // Fetch unverified accounts matching email or phone
        $unverifiedEmailUser = User::where('email', $email)->whereNull('email_verified_at')->first();
        $unverifiedPhoneUser = User::where('phone_number', $phone)->whereNull('email_verified_at')->first();

        // Enforce language preference and hash password
        $data['language_preference'] = $preferredLanguage;
        $data['password'] = Hash::make($data['password']);

        // --- CASE 2: Email account exists but is unverified ---
        if ($unverifiedEmailUser) {
            // Scrub out competing unverified phone holder records if they belong to someone else
            if ($unverifiedPhoneUser && $unverifiedPhoneUser->id !== $unverifiedEmailUser->id) {
                $unverifiedPhoneUser->delete();
            }

            $unverifiedEmailUser->update($data);

            if ($afterSaveCallback) {
                $afterSaveCallback($unverifiedEmailUser);
            }

            $unverifiedEmailUser->sendEmailVerificationNotification();

            return response()->json([
                'message' => __('auth.register_success'),
                'user' => $unverifiedEmailUser,
                'verification_required' => true,
            ], 200);
        }

        // If email is brand new but phone belongs to a stale unverified record, delete that stale record
        if ($unverifiedPhoneUser) {
            $unverifiedPhoneUser->delete();
        }

        // --- CASE 1: Brand New Registration ---
        $user = User::create($data);

        if ($afterSaveCallback) {
            $afterSaveCallback($user);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => __('auth.register_success'),
            'user' => $user,
            'verification_required' => true,
        ], 201);
    }

    // --- CLEANED UP ENDPOINTS ---

    public function registerManager(RegisterManagerRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $lang = $request->getPreferredLanguage(['en', 'ar']) ?? 'en';

        return $this->processRegistration($validated, $lang);
    }

    public function registerClient(RegisterClientRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $lang = $request->getPreferredLanguage(['en', 'ar']) ?? 'en';

        // 1. Extract the store name completely from the data array
        $storeName = $validated['business_name'];

        // 2. Remove it so it doesn't get passed to User::create() or update()
        unset($validated['business_name']);

        // 3. Pass the clean user-only data to the engine
        return $this->processRegistration($validated, $lang, function ($user) use ($storeName) {
            // Run Client specific actions safely using the extracted store name
            Facility::create([
                'facility_name' => $storeName,
                'user_id' => $user->id,
                'facility_type' => 'business',
                'address_id' => 1,//TEMPORARYYYYYYYY
            ]);
        });
    }

    public function registerWorker(RegisterWorkerRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $lang = $request->getPreferredLanguage(['en', 'ar']) ?? 'en';

        // Pre-registration worker validation checks
        $announcement = EmployeeAnnouncement::where('national_id', $validated['national_id'])->first();

        if (!$announcement) {
            return response()->json(['message' => __('auth.employee_not_announced')], 404);
        }
        if ($announcement->claimed) {
            $user = User::where([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'manager_id' => $announcement->manager_id,
                'employmentWarehouse_id' => $announcement->employmentWarehouse_id,
            ])->first();
            if(!$user)
            {
                return response()->json(['message' => __('auth.employee_not_announced')], 403);
            }
            $user->sendEmailVerificationNotification();
            return response()->json(['message' => __('auth.employee_already_registered')], 409);
        }
        if (strtolower(trim($announcement->first_name)) !== strtolower(trim($validated['first_name'])) ||
            strtolower(trim($announcement->last_name)) !== strtolower(trim($validated['last_name']))) {
            return response()->json(['message' => __('auth.employee_identity_mismatch')], 422);
        }

        // Set explicit worker attributes
        $validated['employmentWarehouse_id'] = $announcement->employmentWarehouse_id;
        $validated['role'] = 'worker';
        $validated['manager_id'] = $announcement->manager_id;

        return $this->processRegistration($validated, $lang, function () use ($announcement) {
            // Run Worker specific actions
            $announcement->update(['claimed' => true]);
        });
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

            $user->sendEmailVerificationNotification();

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
            'role' => $user->role,

        ], 200);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', Password::min(10)],
        ], [
            'current_password.required' => __('auth.current_password_required'),
            'current_password.current_password' => __('auth.current_password_incorrect'),
            'new_password.required' => __('auth.new_password_required'),
            'new_password.min' => __('auth.new_password_min'),
        ]);


        $user = $request->user();

        // Update the password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        // Optional but great UX: Revoke all tokens except the current one to log out other devices
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'message' => __('auth.password_changed_success')
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
