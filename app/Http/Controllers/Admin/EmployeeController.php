<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends AdminController
{
    public function getIndex(Request $request)
    {
        return view('admin.employees.index');
    }

    public function getList(Request $request)
    {
        $list = User::where('role_id', 4)
            ->when($request->employee_id, function ($q) use ($request) {
                $q->where('id', $request->employee_id);
            })
            ->when($request->status !== null && $request->status !== '', function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderByDesc('updated_at');

        return \DataTables::of($list)

            ->addColumn('code_name', function ($row) {

                return '[ <b>' . e($row->username) . '</b> ]<br>' . e($row->name);
            })

            ->rawColumns(['code_name'])

            ->make();
    }

    public function getCreate(Request $request)
    {
        return view('admin.employees.create');
    }

    public function postCreate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'country_code' => 'required|string|max:5',
            'mobile' => 'nullable|digits:10|unique:users,mobile',
            'username' => 'required|unique:users,username',
            'password' => 'required|min:6|confirmed',
            'date_of_joining' => 'nullable|date',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {

            $employee = new User;
            $employee->type = 'employee';
            $employee->role_id = 4;
            $employee->name = $request->name;
            $employee->email = strtolower($request->email);
            $employee->country_code = $request->country_code;
            $employee->mobile = $request->mobile;
            $employee->username = strtolower($request->username);
            $employee->password = bcrypt($request->password);
            $employee->date_of_joining = $request->date_of_joining;
            $employee->address = $request->address;
            $employee->status = $request->status ?? 1;
            $employee->created_by = auth()->id();
            if ($request->hasFile('profile_image')) {

                $employee->profile_image = uploadFile(
                    'profile_image',
                    128,
                    128,
                    'user'
                );
            }
            $employee->save();

            return response()->json([
                'message' => 'Employee created successfully',
            ]);

        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'message' => 'Failed to process your request',
            ], 500);
        }
    }

    public function getUpdate(Request $request)
    {
        $employee = User::findOrFail($request->id);

        return view('admin.employees.update', compact('employee'));
    }

    public function postUpdate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$request->id}",
            'country_code' => "required|string|max:5",
            'mobile' => "nullable|digits:10|unique:users,mobile,{$request->id}",
            'username' => "required|unique:users,username,{$request->id}",
            'password' => 'nullable|min:6|confirmed',
            'date_of_joining' => 'nullable|date',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $employee = User::findOrFail($request->id);
            $employee->type = 'employee';
            $employee->role_id = 4;
            $employee->name = $request->name;
            $employee->email = strtolower($request->email);
            $employee->country_code = $request->country_code;
            $employee->mobile = $request->mobile;
            $employee->username = strtolower($request->username);
            if ($request->filled('password')) {

                $employee->password = bcrypt($request->password);
            }
            $employee->date_of_joining = $request->date_of_joining;
            $employee->address = $request->address;
            $employee->status = $request->status ?? 1;
            $employee->modified_by = auth()->id();
            if ($request->hasFile('profile_image')) {
                $employee->profile_image = uploadFile(
                    'profile_image',
                    128,
                    128,
                    'user',
                    $employee->profile_image
                );
            }

            $employee->save();
            return response()->json([
                'message' => 'Employee updated successfully',
            ]);

        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'message' => 'Failed to process your request',
            ], 500);
        }
    }

    public function getDelete(Request $request)
    {
        $employee = User::findOrFail($request->id);
        $employee->delete();
        return response()->json([
            'message' => 'Your request processed successfully.',
        ]);
    }

    public function getChangeStatus(Request $request)
    {
        $employee = User::findOrFail($request->id);
        if (!blank($employee)) {
            $employee->status = (int)!$employee->status;
            $employee->save();
        }

        return response()->json([
            'message' => 'Your request processed successfully.',
        ]);
    }
}