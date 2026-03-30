<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoryController extends AdminController
{
    public function getIndex(Request $request)
    {
        return view('admin.blog_categories.index');
    }

    public function getList(Request $request)
    {
        $list = BlogCategory::query()
            ->when($request->id, function ($q) use ($request) {
                $q->where('id', $request->id);
            })
            ->when($request->slug, function ($q) use ($request) {
                $q->where('slug', $request->slug);
            })
            ->orderBy('id', 'desc');

        return \DataTables::of($list)->make();
    }

    public function getCreate()
    {
        return view('admin.blog_categories.create');
    }

    public function postCreate(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:blog_categories,name',
            'slug' => 'nullable|unique:blog_categories,slug',
        ]);

        try {

            BlogCategory::create([
                'name' => $request->name,
                'slug' => \Str::slug($request->slug ?? $request->name),
            ]);

            return response()->json(['message' => 'Blog category created successfully']);
        } catch (\Throwable $e) {
            \Log::error($e);

            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function getUpdate(Request $request)
    {
        $blogCategory = BlogCategory::findOrFail($request->id);

        return view('admin.blog_categories.update', compact('blogCategory'));
    }

    public function postUpdate(Request $request, $id)
    {
        $blogCategory = BlogCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:blog_categories,name,'.$blogCategory->id,
            'slug' => 'required|unique:blog_categories,slug,'.$blogCategory->id,
        ]);

        $blogCategory->update([
            'name' => $request->name,
            'slug' => \Str::slug($request->slug),
        ]);

        return response()->json(['message' => 'Blog category updated successfully']);
    }

    public function getDelete(Request $request)
    {
        $blogCategory = \App\Models\BlogCategory::findOrFail($request->id);

        $blogCategory->delete();

        return response()->json(['message' => 'Your request processed successfully.']);
    }

    public function getChangeStatus(Request $request)
    {
        $blogCategory = \App\Models\BlogCategory::findOrFail($request->id);
        if (! blank($blogCategory)) {
            $blogCategory->status = (int) ! $blogCategory->status;
            $blogCategory->save();
        }

        return response()->json(['message' => 'Your request processed successfully.']);
    }
}
