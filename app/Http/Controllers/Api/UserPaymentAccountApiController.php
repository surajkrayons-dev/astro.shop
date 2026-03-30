<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPaymentAccount;
use Illuminate\Http\Request;

class UserPaymentAccountApiController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $accounts = UserPaymentAccount::where('user_id', $user->id)->get();

        return response()->json([
            'success' => true,
            'data' => $accounts
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:upi,bank',
            'account_holder_name' => 'required|string|max:255',

            // UPI
            'upi_id' => 'required_if:type,upi|string|max:255',

            // Bank
            'bank_name' => 'required_if:type,bank|string|max:255',
            'account_number' => 'required_if:type,bank|string|max:50',
            'ifsc_code' => 'required_if:type,bank|string|max:20',
        ]);

        $user = auth()->user();

        UserPaymentAccount::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'account_holder_name' => $request->account_holder_name,
            'upi_id' => $request->upi_id,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment account added successfully'
        ], 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'type' => 'required|in:upi,bank',
            'account_holder_name' => 'required|string|max:255',
            'upi_id' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
        ]);

        $user = auth()->user();

        $account = UserPaymentAccount::where('user_id', $user->id)->firstOrFail();

        $account->update([
            'type' => $request->type,
            'account_holder_name' => $request->account_holder_name,
            'upi_id' => $request->upi_id,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment account updated successfully'
        ]);
    }

    public function destroy()
    {
        $user = auth()->user();

        UserPaymentAccount::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment account deleted successfully'
        ]);
    }
}
