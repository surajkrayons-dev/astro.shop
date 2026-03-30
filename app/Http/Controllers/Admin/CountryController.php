<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class CountryController extends AdminController
{
    public function getIndex(Request $request)
    {
        \Can::access('countries');

        return view('admin.countries.index');
    }

    public function getList(Request $request)
    {
        $list = \App\Models\Country::select('*');

        return \DataTables::of($list)->make();
    }

    public function getCreate(Request $request)
    {
        \Can::access('countries', 'create');

        return view('admin.countries.create');
    }

    public function postCreate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'country_name' => 'required|unique:countries,name',
            'status' => 'nullable|in:1,0',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $dataObj = objFromPost(['country_name', 'status']);

        $dataObj->created_by = auth()->id();

        try {
            $country = new \App\Models\Country();
            $country->name = $dataObj->country_name;
            $country->status = (int)($dataObj->status == 1);
            $country->created_by = $dataObj->created_by;
            $country->save();

            return response()->json(['message' => 'Your request processed successfully.']);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 422);
        }
    }

    public function getUpdate(Request $request)
    {
        \Can::access('countries', 'update');

        $country = \App\Models\Country::findOrFail($request->id);
        return view('admin.countries.update', compact('country'));
    }

    public function postUpdate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'country_name' => "required|unique:countries,name,{$request->id},id",
            'status' => 'nullable|in:1,0',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }
        $dataObj = objFromPost(['country_name', 'status']);

        $dataObj->modified_by = auth()->id();

        try {
            $country = \App\Models\Country::find($request->id);
            if (!blank($country)) {
                $country->name = $dataObj->country_name;
                $country->status = (int)($dataObj->status == 1);
                $country->modified_by = $dataObj->modified_by;
                $country->save();
            }

            return response()->json(['message' => 'Your request processed successfully.']);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 422);
        }
    }

    public function getDelete(Request $request)
    {
        \Can::access('countries', 'delete');

        \App\Models\Country::whereId($request->id)->delete();
        return response()->json(['message' => 'Your request processed successfully.']);
    }

    public function getChangeStatus(Request $request)
    {
        \Can::access('countries', 'update');

        $country = \App\Models\Country::findOrFail($request->id);
        if (!blank($country)) {
            $country->status = (int)!$country->status;
            $country->save();
        }

        return response()->json(['message' => 'Your request processed successfully.']);
    }
}
