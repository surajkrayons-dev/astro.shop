<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use App\Models\User;

class EmployeeAffiliateApiController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'country_code' => 'required|string|max:5',
            'mobile' => 'required|digits:10|unique:users,mobile',
            'username' => 'required|unique:users,username',

            'company_name' => 'nullable|string|max:255',
            'affiliate_type' => 'required|in:blogger,influencer,agency,publisher,other',
            'traffic_sources' => 'nullable|array',
            'promotion_plan' => 'nullable|string',
            'expected_leads' => 'nullable|in:less_than_50,50_100,100_500,500_plus',

            'password' => 'required|min:6|confirmed',
            'profile_image' => 'nullable|string',
            'terms_accepted' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {

            $employee = User::create([
                'type' => 'employee',
                'role_id' => 4,

                'name' => trim($request->name),
                'email' => strtolower(trim($request->email)),
                'country_code' => $request->country_code,
                'mobile' => $request->mobile,
                'username' => strtolower(trim($request->username)),
                'password' => bcrypt($request->password),

                'company_name' => $request->company_name,
                'affiliate_type' => $request->affiliate_type,
                'traffic_sources' => $request->traffic_sources,
                'promotion_plan' => $request->promotion_plan,
                'expected_leads' => $request->expected_leads,

                'commission_percentage' => 5,
                'terms_accepted' => $request->terms_accepted,
                'status' => 0,
            ]);

            if ($request->filled('profile_image')) {

                $employee->profile_image = $this->saveBase64Image(
                    $request->profile_image,
                    'user'
                );

                $employee->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'Affiliate registration submitted successfully. Waiting for approval.',
                'employee' => $employee->only([
                    'id',
                    'name',
                    'email',
                    'country_code',
                    'mobile',
                    'username',
                    'company_name',
                    'affiliate_type',
                    'traffic_sources',
                    'promotion_plan',
                    'expected_leads',
                    'commission_percentage',
                    'profile_image',
                ]),
            ]);

        } catch (\Throwable $th) {

            \Log::error($th);

            return response()->json([
                'status' => false,
                'message' => 'Failed to process your request.',
            ], 500);
        }
    }
}
