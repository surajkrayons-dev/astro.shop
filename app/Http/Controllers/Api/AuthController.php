<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\User;
use App\Models\LoginRequest;
use Hash;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Mail\OtpNotification;
use Mail;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Mail\NewRegistrationEmail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class AuthController extends Controller
{

    // login with Password
    public function login(Request $request)
    {
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();

            if ($user->isPromoter()) {

                // ✅ Sirf is user ke purane verified login requests ko logout karo
                \App\Models\LoginRequest::where('user_id', $user->id)
                    ->where('status', 'verified')
                    ->update(['status' => 'logged_out']);

                // ✅ Sirf is user ke purane Sanctum tokens delete karo
                $user->tokens()->delete();

                // ✅ Naya hash_token generate karo
                $newHashToken = now()->timestamp . getRandomNumber(15);

                // ✅ User ke table me device_token + hash_token update karo
                $updateData = ['hash_token' => $newHashToken];
                if ($request->filled('device_token')) {
                    $updateData['device_token'] = $request->device_token;
                }
                $user->update($updateData);

                // ✅ Naya Sanctum token banao
                $success['token']      = $user->createToken('MyApp')->plainTextToken;
                $success['name']       = $user->name;
                $success['avatar']     = '/storage/user/' . $user->avatar;
                // $success['hash_token'] = $newHashToken; // 👈 latest hash_token bhi response me bhej rahe

                // ✅ Naya login request create karo
                $login_request = new \App\Models\LoginRequest();
                $login_request->user_id    = $user->id;
                $login_request->status     = 'verified';
                $login_request->hash_token = $newHashToken;
                $login_request->save();

                return response()->json([
                    'success' => true,
                    'message' => 'User logged in successfully',
                    'data'    => $success,
                ], 200);

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Only promoter users can log in'
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Your email or password is incorrect'
            ], 401);
        }
    }

    // Logout for user
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            // Invalidate all tokens
            $user->tokens()->delete();

            // Update the latest verified login request to logged_out
            $latestLogin = LoginRequest::where('user_id', $user->id)
                            ->where('status', 'verified')
                            ->latest()
                            ->first();

            if ($latestLogin) {
                $latestLogin->status = 'logged_out';
                $latestLogin->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully'
            ], 200); // 200 success

        } else {

            return response()->json([
                'success' => false,
                'message' => 'No authenticated user found'
            ], 400); // 400 Bad Request

        }
    }


    // [not in use]
    public function login_old(Request $request)
    {
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();

            if ($user->isPromoter()) {
                // Check for existing verified login request
                $existingLogin = \App\Models\LoginRequest::where('user_id', $user->id)
                    ->where('status', 'verified')
                    ->first();

                if ($existingLogin) {
                    $existingLogin->status = 'logged_out';
                    $existingLogin->save();
                }

                $success['token'] = $user->createToken('MyApp')->plainTextToken;
                $success['name'] = $user->name;
                $success['avatar'] = '/storage/user/' . $user->avatar;


                $login_request = new \App\Models\LoginRequest();
                $login_request->user_id = $user->id;
                $login_request->status = 'verified';
                $login_request->hash_token = now()->timestamp . getRandomNumber(15);

                // if ($request->input('image') != '') {

                //     // Delete the existing avatar
                //     if ($login_request->image != '') {
                //         Storage::delete('public/login_request/' . $user->image);
                //     }

                //     // Decode base64 image data
                //     $base64Image = $request->input('image');
                //     $data = explode(',', $base64Image);
                //     $imageData = base64_decode($data[1]);

                //     // Generate a unique file name
                //     $fileImageName = uniqid() . '.png';

                //     // Store the image file
                //     Storage::put('public/login_request/' . $fileImageName, $imageData);

                //     // Update the user's avatar
                //     $login_request->image = $fileImageName;
                // }

                $login_request->save();


                return response()->json([
                    'success' => true,
                    'message' => 'User logged in successfully',
                    'data' => $success,
                ], 200); // 200 success

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Only promoter users can log in'
                ], 400); // 400 Bad Request
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Your email or password is incorrect'
            ], 401); // 401 Unauthorized
        }
    }

    // [not in use]
    public function updateDeviceToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 400); // 400 Bad Request
        }

        $user = Auth::user(); // Get the authenticated user
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401); // 401 Unauthorized
        }

        $user->update(['device_token' => $request->device_token]);

        return response()->json(['message' => 'Device token updated successfully!'], 200); // 200 success
    }



}