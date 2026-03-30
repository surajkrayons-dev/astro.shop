<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class RoleController extends AdminController
{
    public function getIndex(Request $request)
    {
        \Can::access('roles');

        return view('admin.roles.index');
    }

    public function getList(Request $request)
    {
        $list = \App\Models\Role::where('id', '<>', 1)->where('id', '<>', 2)->where('id', '<>', 3);

        return \DataTables::of($list)->make();
    }

    public function getCreate(Request $request)
    {
        \Can::access('roles', 'create');

        return view('admin.roles.create');
    }

    public function postCreate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'role_name' => 'required|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $dataObj = objFromPost(['role_name', 'permissions']);

        try {
            $permissions = [];
            foreach ($dataObj->permissions as $permission => $actions) {
                $permissions[$permission] = array_keys($actions);
            }

            $role = new \App\Models\Role();
            $role->name = $dataObj->role_name;
            $role->permissions = json_encode($permissions);
            $role->save();

            return response()->json(['message' => 'Your request processed successfully.']);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 422);
        }
    }

    public function getUpdate(Request $request)
    {
        \Can::access('roles', 'update');

        $role = \App\Models\Role::findOrFail($request->id);
        $role->permissions = json_decode($role->permissions, true);
        return view('admin.roles.update', compact('role'));
    }

    public function postUpdate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'role_name' => "required|unique:roles,name,{$request->id},id",
            'permissions' => 'required|array',
            'permissions.*' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $dataObj = objFromPost(['role_name', 'permissions']);

        try {
            $role = \App\Models\Role::find($request->id);
            if (!blank($role)) {
                $permissions = [];
                foreach ($dataObj->permissions as $permission => $actions) {
                    $permissions[$permission] = array_keys($actions);
                }

                $role->name = $dataObj->role_name;
                $role->permissions = json_encode($permissions);
                $role->save();
            }

            return response()->json(['message' => 'Your request processed successfully.']);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 422);
        }
    }

    public function getDelete(Request $request)
    {
        \Can::access('roles', 'delete');

        \App\Models\Role::whereId($request->id)->delete();
        return response()->json(['message' => 'Your request processed successfully.']);
    }
}
