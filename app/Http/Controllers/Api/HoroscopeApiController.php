<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horoscope;
use Illuminate\Http\Request;

class HoroscopeApiController extends Controller
{
    // All horoscopes
    public function index(Request $request)
    {
        $query = Horoscope::with('zodiac')
            ->where('status', 1);

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->zodiac_id) {
            $query->where('zodiac_id', $request->zodiac_id);
        }

        return response()->json([
            'status' => true,
            'data' => $query->orderBy('date', 'desc')->get(),
        ]);
    }

    // Filter horoscope
    public function show($id)
    {
        $data = Horoscope::with('zodiac')
            ->where('status', 1)
            ->find($id);

        if (! $data) {
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }
}
