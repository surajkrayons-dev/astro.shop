<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoryApiController extends Controller
{
    public function index()
    {
        $blogCategory = BlogCategory::orderBy('id', 'desc')->get();

        return response()->json([
            'status' => true,
            'data'   => $blogCategory,
        ]);
    }

    public function show($id)
    {
        $blogCategory = BlogCategory::find($id);

        if (! $blogCategory) {
            return response()->json([
                'status'  => false,
                'message' => 'Blog Category not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $blogCategory,
        ]);
    }
}
