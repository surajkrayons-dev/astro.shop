<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Hash;
use Illuminate\Support\Facades\Validator;

use App\Exports\LoginRequestExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Maatwebsite\Excel\Validators\ValidationException;

class LoginRequestController extends AdminController
{
    public function getIndex(Request $request)
    {
        \Can::access('login_requests');

        return view('admin.login_requests.index');
    }

    // public function getList(Request $request)
    // {
    //     $list = \App\Models\LoginRequest::select('login_requests.id', 'login_requests.created_at', 'login_requests.status', 'users.name AS name', 'users.name AS username')
    //         ->leftJoin('users', 'users.id', '=', 'login_requests.user_id');

    //     return \DataTables::of($list)->make();
    // }
    public function getList(Request $request)
    {
        $list = \App\Models\LoginRequest::select(
                'login_requests.id',
                'login_requests.created_at',
                'login_requests.status',
                'users.name AS name',
                'users.username AS username'
            )
            ->leftJoin('users', 'users.id', '=', 'login_requests.user_id')
             ->when($request->filled('user'), function ($query) use ($request) {
                $search = $request->user;
                return $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('login_requests.status', $request->status);
            })
            ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                return $query->whereBetween(\DB::raw('DATE(login_requests.created_at)'), [$request->start_date, $request->end_date]);
            })
            ->orderByDesc('login_requests.updated_at');

        return \DataTables::of($list)->make(true);
    }


    public function postStatusUpdate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|in:verified,rejected,logged_out',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $dataObj = objFromPost(['id', 'status']);

        try {
            $login_request = \App\Models\LoginRequest::find($dataObj->id);
            if (
                $login_request &&
                ($login_request->status == 'pending' && in_array($dataObj->status, ['verified', 'rejected'])) ||
                ($login_request->status == 'verified' && $dataObj->status == 'logged_out')
            ) {
                \App\Models\LoginRequest::where('id', $dataObj->id)->update(['status' => $dataObj->status]);

                // Revoke tokens if status is 'logged_out'
                if ($dataObj->status == 'logged_out') {
                    $user = \App\Models\User::find($login_request->user_id); // Assuming user_id is available
                    if ($user) {
                        $user->tokens()->delete(); // Revoke all tokens
                    }
                }
            }

            return response()->json(['message' => 'Your request processed successfully.']);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 422);
        }
    }


    // Login Report Fns
    public function getReportIndex(Request $request)
    {
        \Can::access('login_report');

        $authUser = auth()->user();

        // Get client IDs and subordinate IDs
        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
        $allowedUserIds = array_merge([$authUser->id], $subordinateIds);

        $users = \App\Models\User::select('id', 'name', 'username')
            ->when($authUser->role_id != 1, function ($query) use ($allowedUserIds) {
                $query->whereIn('id', $allowedUserIds);
            })
            ->orderBy('name')
            ->orderBy('username')
            ->get();

        return view('admin.login_requests.login_report', compact('users'));
    }

    public function getReportList(Request $request)
    {
        $authUser = auth()->user();

        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
        $allowedUserIds = array_unique(array_merge([$authUser->id], $subordinateIds));

        $list = \App\Models\LoginRequest::select('login_requests.created_at', 'login_requests.status', 'users.name AS name', 'users.username AS username')
            ->leftJoin('users', 'users.id', '=', 'login_requests.user_id')
            ->when($authUser->role_id != 1, function ($query) use ($allowedUserIds) {
                $query->whereIn('login_requests.user_id', $allowedUserIds);
            })
            ->when($request->user_id, function ($builder) use ($request) {
                $builder->where('login_requests.user_id', $request->user_id);
            })
            ->when($request->status, function ($builder) use ($request) {
                $builder->where('login_requests.status', $request->status);
            })
            ->when($request->start_date && $request->end_date, function ($builder) use ($request) {
                $builder->whereBetween(\DB::raw('DATE(login_requests.created_at)'), [$request->start_date, $request->end_date]);
            })
            ->orderByDesc('login_requests.created_at');

        return \DataTables::of($list)->make();
    }



    // Export Xlsx
    public function exportXlsx()
    {
        $fileName = 'login_requests_' . date('Y_m_d_H_i_s') . '.xlsx';
        return Excel::download(new LoginRequestExport, $fileName);
    }
}
