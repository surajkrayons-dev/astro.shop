<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\Horoscope;
use App\Models\ZodiacSign;
use Illuminate\Http\Request;

class HoroscopeController extends AdminController
{
    public function getIndex(Request $request)
    {
        return view('admin.horoscopes.index');
    }

    public function getList(Request $request)
    {
        $list = Horoscope::select('horoscopes.*')
            ->with('zodiac')
            ->when($request->zodiac_id, function ($q) use ($request) {
                $q->where('zodiac_id', $request->zodiac_id);
            })
            ->when($request->type, function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->when($request->status !== null && $request->status !== '', function ($q) use ($request) {
                $q->where('horoscopes.status', $request->status);
            })
            ->orderBy('horoscopes.date', 'desc');

        return \DataTables::of($list)
            ->addColumn('zodiac_name', fn ($row) => $row->zodiac->name ?? '')
            ->addColumn('status_text', fn ($row) => $row->status == 1 ? 'Active' : 'Inactive')
            ->rawColumns(['zodiac_name'])
            ->make();
    }

    public function getCreate(Request $request)
    {
        // \Can::access('stores', 'create');
        $zodiacs = ZodiacSign::where('status', 1)->get();

        return view('admin.horoscopes.create', compact('zodiacs'));
    }

    public function postCreate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'zodiac_id' => 'required|exists:zodiac_signs,id',
            'type' => 'required|in:today,yesterday,tomorrow,daily,weekly,monthly,yearly',
            'date' => 'required|date',
            'title' => 'required',
            'description' => 'nullable|string',
            'love' => 'nullable|string',
            'career' => 'nullable|string',
            'health' => 'nullable|string',
            'finance' => 'nullable|string',
            'lucky_number' => 'nullable|string',
            'lucky_color' => 'nullable|string',
            'status' => 'nullable|in:1,0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $horoscope = new \App\Models\Horoscope;
            $horoscope->zodiac_id = $request->zodiac_id;
            $horoscope->type = $request->type;
            $horoscope->date = $request->date;
            $horoscope->title = $request->title;
            $horoscope->description = $request->description;
            $horoscope->love = $request->love;
            $horoscope->career = $request->career;
            $horoscope->health = $request->health;
            $horoscope->finance = $request->finance;
            $horoscope->lucky_number = $request->lucky_number;
            $horoscope->lucky_color = $request->lucky_color;
            $horoscope->status = $request->status ?? 1;
            $horoscope->created_by = auth()->id();
            $horoscope->save();

            return response()->json(['message' => 'Horoscope created successfully.']);
        } catch (\Throwable $th) {
            \Log::error($th);

            return response()->json(['message' => 'Failed to create horoscope. Please try again later.'], 422);
        }
    }

    public function getUpdate(Request $request)
    {
        $horoscope = Horoscope::findOrFail($request->id);
        $zodiacs = ZodiacSign::where('status', 1)->get();

        return view('admin.horoscopes.update', compact('horoscope', 'zodiacs'));
    }

    public function postUpdate(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'zodiac_id' => 'required|exists:zodiac_signs,id',
            'type' => 'required|in:today,yesterday,tomorrow,daily,weekly,monthly,yearly',
            'date' => 'required|date',
            'title' => 'required',
            'description' => 'nullable|string',
            'love' => 'nullable|string',
            'career' => 'nullable|string',
            'health' => 'nullable|string',
            'finance' => 'nullable|string',
            'lucky_number' => 'nullable|string',
            'lucky_color' => 'nullable|string',
            'status' => 'nullable|in:1,0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $horoscope = \App\Models\Horoscope::findOrFail($id);

            $horoscope->zodiac_id = $request->zodiac_id;
            $horoscope->type = $request->type;
            $horoscope->date = $request->date;
            $horoscope->title = $request->title;
            $horoscope->description = $request->description;
            $horoscope->love = $request->love;
            $horoscope->career = $request->career;
            $horoscope->health = $request->health;
            $horoscope->finance = $request->finance;
            $horoscope->lucky_number = $request->lucky_number;
            $horoscope->lucky_color = $request->lucky_color;
            $horoscope->status = $request->status ?? 1;
            $horoscope->modified_by = auth()->id();

            $horoscope->save();

            return response()->json([
                'message' => 'Horoscope updated successfully.',
            ]);

        } catch (\Throwable $th) {

            \Log::error($th);

            return response()->json([
                'message' => 'Failed to update horoscope. Please try again later.',
            ], 500);
        }
    }

    public function getDelete(Request $request)
    {
        // \Can::access('stores', 'delete');
        $horoscope = \App\Models\Horoscope::findOrFail($request->id);

        $horoscope->delete();

        return response()->json(['message' => 'Your request processed successfully.']);
    }

    public function getChangeStatus(Request $request)
    {
        \Can::access('stores', 'update');

        $store = \App\Models\Store::findOrFail($request->id);
        if (! blank($store)) {
            $store->status = (int) ! $store->status;
            $store->save();
        }

        return response()->json(['message' => 'Your request processed successfully.']);
    }
}
