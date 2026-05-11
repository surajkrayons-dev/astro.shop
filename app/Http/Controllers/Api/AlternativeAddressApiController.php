<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\users;
use App\Models\AlternativeAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlternativeAddressApiController extends Controller
{
    public function index(Request $request)
    {
        $addresses = AlternativeAddress::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $addresses
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'email'             => 'nullable|email|max:255',
            'country_code'       => 'required|string|max:5',
            'mobile'             => 'required|string|max:20',
            'alternative_mobile' => 'nullable|string|max:20',
            'city'               => 'required|string|max:255',
            'state'              => 'required|string|max:255',
            'country'            => 'required|string|max:255',
            'address'            => 'required|string',
            'pincode'            => 'required|string|max:10',
            'by_default'         => 'nullable|in:0,1',
        ]);

        if ($request->by_default == 1) {

            AlternativeAddress::where('user_id', $request->user()->id)
                ->update(['by_default' => 0]);
        }

        $address = AlternativeAddress::create([
            'user_id'            => $request->user()->id,
            'email'              => $request->email,
            'name'               => $request->name,
            'country_code'       => $request->country_code ?? '+91',
            'mobile'             => $request->mobile,
            'alternative_mobile' => $request->alternative_mobile,
            'city'               => $request->city,
            'state'              => $request->state,
            'country'            => $request->country,
            'address'            => $request->address,
            'pincode'            => $request->pincode,
            'by_default'         => $request->by_default ?? 0,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Address added successfully',
            'data' => $address
        ]);
    }

    public function show(Request $request, $id)
    {
        $address = AlternativeAddress::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => $address
        ]);
    }

    public function update(Request $request, $id)
    {
        $address = AlternativeAddress::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'name'               => 'required|string|max:255',
            'email'             => 'nullable|email|max:255',
            'country_code'       => 'required|string|max:5',
            'mobile'             => 'required|string|max:20',
            'alternative_mobile' => 'nullable|string|max:20',
            'city'               => 'required|string|max:255',
            'state'              => 'required|string|max:255',
            'country'            => 'required|string|max:255',
            'address'            => 'required|string',
            'pincode'            => 'required|string|max:10',
            'by_default'         => 'nullable|in:0,1',
        ]);

        if ($request->by_default == 1) {

            AlternativeAddress::where('user_id', $request->user()->id)
                ->where('id', '!=', $address->id)
                ->update(['by_default' => 0]);
        }

        $address->update([
            'name'               => $request->name,
            'email'             => $request->email,
            'country_code'       => $request->country_code ?? '+91',
            'mobile'             => $request->mobile,
            'alternative_mobile' => $request->alternative_mobile,
            'city'               => $request->city,
            'state'              => $request->state,
            'country'            => $request->country,
            'address'            => $request->address,
            'pincode'            => $request->pincode,
            'by_default'        => $request->by_default ?? $address->by_default,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Address updated successfully',
            'data' => $address
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $address = AlternativeAddress::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $address->delete();

        return response()->json([
            'status' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    public function getPincodeData(Request $request)
    {
        $request->validate([
            'pincode' => 'required|digits:6'
        ]);

        $data = DB::table('india_pincodes')
            ->where('pincode', $request->pincode)
            ->select(
                'office_name',
                'district as city',
                'state',
                'state_code',
                'pincode'
            )
            ->distinct()
            ->get();

        if ($data->isEmpty()) {

            return response()->json([
                'status' => false,
                'message' => 'Pincode not found'
            ]);
        }

        return response()->json([
            'status' => true,
            'country' => 'India',
            'data' => $data
        ]);
    }
}