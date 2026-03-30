<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\Banner;
use Illuminate\Http\Request;
use DB;

class AstroBannerController extends AdminController
{
    public function getIndex(Request $request)
    {
        return view('admin.astro_banners.index');
    }

    public function getList(Request $request)
    {
        $list = Banner::query()
            ->where('type', 'astro')
            ->select([
                'id',
                'media',
                'status',
                'sort_order',
                'created_at'
            ])
            ->when($request->status !== null && $request->status !== "",
                fn($q) => $q->where('status', $request->status)
            )
            ->orderBy('id', 'desc');

        return \DataTables::of($list)
            ->addColumn('preview', function ($row) {

                if (!$row->media) return '-';

                $media = $row->media;
                $url = asset('storage/' . $media['path']);

                if ($media['type'] === 'video') {
                    return '<video width="120" height="70" controls>
                                <source src="'.$url.'">
                            </video>';
                }

                return '<img src="'.$url.'" width="120">';
            })
            ->rawColumns(['preview'])
            ->make();
    }

    public function getCreate()
    {
        return view('admin.astro_banners.create');
    }

    public function postCreate(Request $request)
    {
        $request->validate([
            'media' => 'required|file|mimes:jpg,jpeg,png,webp,mp4,webm|max:20480',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:0,1'
        ]);

        DB::beginTransaction();

        try {

            $media = uploadMedia('media', null, null, 'banners');

            Banner::create([
                'type'       => 'astro',
                'media'      => $media,
                'status'     => (int) $request->input('status', 1),
                'sort_order' => $request->sort_order ?? 0,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Astro banner created successfully'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();
            \Log::error($e);

            return response()->json([
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function getUpdate(Request $request)
    {
        $banner = Banner::where('type','astro')->findOrFail($request->id);

        return view('admin.astro_banners.update', compact('banner'));
    }

    public function postUpdate(Request $request, $id)
    {
        $banner = Banner::where('type','astro')->findOrFail($id);

        $request->validate([
            'media' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,webm|max:20480',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:0,1'
        ]);

        DB::beginTransaction();

        try {

            if ($request->hasFile('media')) {

                $media = uploadMedia(
                    'media',
                    null,
                    null,
                    'banners',
                    $banner->media['path'] ?? null
                );

                $banner->media = $media;
            }

            $banner->sort_order = $request->sort_order ?? 0;
            $banner->status     = (int) $request->input('status', 1);

            $banner->save();

            DB::commit();

            return response()->json([
                'message' => 'Astro banner updated successfully'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();
            \Log::error($e);

            return response()->json([
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function getDelete(Request $request)
    {
        $banner = Banner::where('type','astro')->findOrFail($request->id);

        if ($banner->media && isset($banner->media['path'])) {
            $path = public_path('storage/' . $banner->media['path']);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $banner->delete();

        return response()->json([
            'message' => 'Astro banner deleted successfully'
        ]);
    }

    public function getChangeStatus(Request $request)
    {
        $banner = Banner::where('type','astro')->findOrFail($request->id);

        $banner->status = !$banner->status;
        $banner->save();

        return response()->json([
            'message' => 'Status updated successfully'
        ]);
    }
}