<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryApiController extends Controller
{
    /**
     * GET ALL CATEGORIES
     * Optional filters:
     *  - ?slug=puja-items
     *  - ?id=3
     */
    public function index(Request $request)
    {
        $query = Category::query();

        $query->where('status', 1);

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('slug')) {
            $query->where('slug', $request->slug);
        }

        $categories = $query
            ->select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => true,
            'count'  => $categories->count(),
            'data'   => $categories,
        ]);
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->where('status', 1)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => $category,
        ]);
    }
}
