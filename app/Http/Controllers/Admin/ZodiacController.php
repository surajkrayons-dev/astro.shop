<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\ZodiacSign;
use Illuminate\Http\Request;

class ZodiacController extends AdminController
{
    public function getIndex(Request $request)
    {
        return view('admin.zodiac_signs.index');
    }

    public function getList(Request $request)
    {
        $list = ZodiacSign::query()
            ->when($request->zodiac_id, function ($q) use ($request) {
                $q->where('id', $request->zodiac_id);
            })
            ->when($request->status !== null && $request->status !== '', function ($q) use ($request) {
                $q->where('zodiac_signs.status', $request->status);
            })
            ->orderBy('id', 'desc');

        return \DataTables::of($list)
            ->addColumn('icon', function ($row) {
                return $row->icon ? asset('storage/zodiac/'.$row->icon) : null;
            })
            ->addColumn('status_text', function ($row) {
                return $row->status ? 'Active' : 'Inactive';
            })
            ->rawColumns(['icon'])
            ->make();
    }

    public function getCreate()
    {
        return view('admin.zodiac_signs.create');
    }

    public function postCreate(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:zodiac_signs,name',
            'slug' => 'nullable|unique:zodiac_signs,slug',
            'icon' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'description' => 'nullable|string',
            'status' => 'nullable|in:0,1',
        ]);

        try {
            $iconPath = null;

            if ($request->hasFile('icon')) {
                $iconPath = uploadFile('icon', '128', '128', 'zodiac');
            }

            ZodiacSign::create([
                'name' => $request->name,
                'slug' => \Str::slug($request->slug ?? $request->name),
                'icon' => $iconPath,
                'description' => $request->description,
                'status' => $request->status ?? 1,
            ]);

            return response()->json(['message' => 'Zodiac created successfully']);
        } catch (\Throwable $e) {
            \Log::error($e);

            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function getUpdate(Request $request)
    {
        $zodiac = ZodiacSign::findOrFail($request->id);

        return view('admin.zodiac_signs.update', compact('zodiac'));
    }

    public function postUpdate(Request $request, $id)
    {
        $zodiac = ZodiacSign::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:zodiac_signs,name,'.$zodiac->id,
            'slug' => 'required|unique:zodiac_signs,slug,'.$zodiac->id,
            'icon' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'description' => 'nullable|string',
            'status' => 'nullable|in:0,1',
        ]);

        $iconPath = $zodiac->icon;

        if ($request->hasFile('icon')) {
            $iconPath = uploadFile('icon', '128', '128', 'zodiac', $zodiac->icon);
        }

        $zodiac->update([
            'name' => $request->name,
            'slug' => \Str::slug($request->slug),
            'icon' => $iconPath,
            'description' => $request->description,
            'status' => $request->status ?? 1,
        ]);

        return response()->json(['message' => 'Zodiac updated successfully']);
    }

    public function getDelete(Request $request)
    {
        // \Can::access('stores', 'delete');
        $zodiac = \App\Models\ZodiacSign::findOrFail($request->id);

        $zodiac->delete();

        return response()->json(['message' => 'Your request processed successfully.']);
    }

    public function getChangeStatus(Request $request)
    {
        // \Can::access('stores', 'update');

        $zodiac = \App\Models\ZodiacSign::findOrFail($request->id);
        if (! blank($zodiac)) {
            $zodiac->status = (int) ! $zodiac->status;
            $zodiac->save();
        }

        return response()->json(['message' => 'Your request processed successfully.']);
    }
}
