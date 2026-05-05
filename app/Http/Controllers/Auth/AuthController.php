<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Mail\LoginOtpVerifyMail;
use App\Mail\ForgotPasswordMail;

// use Illuminate\Support\Facades\Validator;
// use Laravel\Socialite\Facades\Socialite;
// use Illuminate\Validation\Rule;


use Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (\Auth::user() !== null) {
                // return redirect()->route('admin.dashboard');
            }
            return $next($request);
        });
    }

    public function getLogin(Request $request)
    {
        if (\Auth::check()) {
            $user = \Auth::user();

            switch ($user->role_id) {
                case 1: // Role 1: Admin
                    return redirect()->route('admin.dashboard.index');
                case 2: // Role 2: Astrologer
                    return redirect()->route('astro.dashboard.index');
                default: // Fallback for any undefined roles
                    return redirect()->route('admin.dashboard.index');
            }
        }

        return view('auth.login');
    }

    public function postLogin(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ])->stopOnFirstFailure(true);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $remember = $request->remember_me == 1;
        $loginInput = strtolower($request->username);

        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $fieldType = 'email';
        } elseif (preg_match('/^[0-9]{10}$/', $loginInput)) {
            $fieldType = 'mobile';
        } else {
            $fieldType = 'username';
        }

        $credentials = [
            $fieldType => $loginInput,
            'password' => $request->password
        ];
        

        if (!\Auth::attempt($credentials, $remember)) {
            return response()->json(['message' => 'The combination of username & password is not registered with us.'], 422);
        }

        $user = \Auth::user();
        
        if ($user->status != 1) {
            \Auth::logout();
            return response()->json(['message' => 'Your account status is inactive, Kindly contact to Administrator.'], 422);
        }

        if (!$user->isSuperAdmin() || ($user->isSuperAdmin() && !$user->is_two_factor_auth_enabled)) {
            // Store permission in session
            $permissions = \App\Models\Role::where('id', $user->role_id)->value('permissions');
            $request->session()->put('permissions', $permissions ? json_decode($permissions, true) : null);
        }

        // is_two_factor_auth_enabled
        if ($user->isSuperAdmin() && $user->is_two_factor_auth_enabled) {
            $user->otp = rand(1000, 9999);

            if (blank($user->hash_token)) {
                $user->hash_token = generateRandomString(50);
                $user->save();
            }
            $user->save();

            $dataArr = [
                'name' => $user->name,
                'otp' => $user->otp
            ];
            \App\Helpers\MailHelper::send($user->email, $dataArr, \App\Mail\LoginOtpVerifyMail::class);

            \Auth::logout();
        }

        // Redirecting Url according to role.
        if ($user->isSuperAdmin() || ($user->isSuperAdmin() && $user->is_two_factor_auth_enabled)) {
            return response()->json([
                'message' => 'Redirecting..',
                'redirect_url' => $user->isSuperAdmin() && $user->is_two_factor_auth_enabled ? route("auth.two.factor.index", $user->hash_token) : route("admin.dashboard.index"),
            ]);
        } elseif ($user->isAstro()) {
            return response()->json([
                'message' => 'Redirecting..',
                'redirect_url' => $user->isSuperAdmin() && $user->is_two_factor_auth_enabled ? route("auth.two.factor.index", $user->hash_token) : route("astro.dashboard.index"),
            ]);
        } else {
            return response()->json([
                'message' => 'Redirecting..',
                'redirect_url' => !$user->isSuperAdmin() && $user->is_two_factor_auth_enabled ? route("auth.two.factor.index", $user->hash_token) : route("admin.dashboard.index"),
            ]);
        }
    }

    public function getLogout(Request $request)
    {
        Auth::logout();
        return redirect()->route('admin.dashboard');
    }

    public function getTwoFactorAuthIndex(Request $request)
    {
        $user = \App\Models\User::whereHashToken($request->token)->first();
        if (blank($user)) {
            return redirect()->route('auth.login');
        }

        return view('auth.two_factor_verify', compact('user'));
    }

    public function postVerifyTwoFactorAuth(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'token' => 'required|exists:users,hash_token',
            'otp' => 'required|digits:4',
        ])->stopOnFirstFailure(true);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $dataObj = objFromPost(['token', 'otp']);

        try {
            $user = \App\Models\User::where('hash_token', $dataObj->token)->first();

            if (!$user) {
                return response()->json(['message' => 'Failed to process your request. Please try again later.'], 422);
            }

            \Log::info('Verifying OTP', [
                'db_otp' => $user->otp,
                'submitted_otp' => $dataObj->otp,
            ]);

            if (trim((string) $user->otp) !== trim((string) $dataObj->otp)) {
                return response()->json(['message' => 'Oops! It seems the OTP code you entered is invalid.'], 422);
            }

            $user->otp = null;
            $user->hash_token = null;
            $user->save();

            \Auth::login($user);

            if (\Auth::check()) {
                $permissions = \App\Models\Role::where('id', $user->role_id)->value('permissions');
                $request->session()->put('permissions', $permissions ? json_decode($permissions, true) : null);
            }

            return response()->json(['message' => 'OTP Verified. Redirecting..']);

        } catch (\Throwable $th) {
            \Log::error('2FA Verify Error', ['exception' => $th]);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 422);
        }
    }

    public function getForgotPassword(Request $request)
    {
        return view('auth.forgot_password');
    }

    public function postForgotPassword(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => "required|exists:users",
        ])->stopOnFirstFailure(true);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $dataObj = objFromPost(['email']);

        try {
            $user = \App\Models\User::whereEmail($dataObj->email)->first();
            if (blank($user)) {
                return response()->json(['message' => 'This email is not registered with us.'], 422);
            } elseif ($user->status != 1) {
                return response()->json(['message' => 'Your account status is inactive, Kindly contact to Administrator.'], 422);
            }

            if (blank($user->hash_token)) {
                $user->hash_token = generateRandomString(50);
                // dd($user->hash_token);
                $user->save();
            }

            // Try to send email
            $dataArr = [
                'name' => $user->name,
                'link' => route('auth.password.reset.request', [$user->hash_token])
            ];

            Mail::to($user->email)->send(new ForgotPasswordMail($dataArr));

            return response()->json(['message' => 'We have send an email to your registered email id. Please check it.']);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 422);
        }
    }

    public function getResetPassword(Request $request)
    {
        $user = \App\Models\User::whereHashToken($request->token)->first();
        if (blank($user)) {
            exit('This link has expired.');
        }

        return view('auth.reset_password', compact('user'));
    }

    public function postResetPassword(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'hash_token' => 'required|exists:users',
            'new_password' => 'required|confirmed|min:6',
        ])->stopOnFirstFailure(true);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $dataObj = objFromPost(['hash_token', 'new_password']);

            $user = \App\Models\User::whereHashToken($dataObj->hash_token)->first();
            if ($user) {
                $user->password = bcrypt($dataObj->new_password);
                $user->hash_token = null;
                $user->save();
            }

            return response()->json(['message' => 'You have successfully changed your password.']);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 422);
        }
    }

}