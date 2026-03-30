<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Blog::with('category');

        if ($request->blog_category_id) {
            $query->where('blog_category_id', $request->blog_category_id);
        }

        $blogs = $query->orderBy('date', 'desc')->get();

        $blogs->transform(function ($blog) {
            $blog->image_url = $blog->image
                ? asset('storage/blog/' . $blog->image)
                : null;

            return $blog;
        });

        return response()->json([
            'status' => true,
            'data'   => $blogs,
        ]);
    }

    public function show($id)
    {
        $blog = Blog::with('category')->find($id);

        if (! $blog) {
            return response()->json([
                'status'  => false,
                'message' => 'Blog not found',
            ], 404);
        }

        $blog->image_url = $blog->image
            ? asset('storage/blog/' . $blog->image)
            : null;

        return response()->json([
            'status' => true,
            'data'   => $blog,
        ]);
    }
}
