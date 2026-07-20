<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\Banner;
use Illuminate\Http\Request;
use DB;

class StoreBannerController extends AdminController
{
    public function getIndex(Request $request)
    {
        return view('admin.store_banners.index');
    }

    public function getList(Request $request)
    {
        $list = Banner::query()
            ->where('type', 'store')
            ->select([
                'id',
                'media',
                'url',
                'display_duration',
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

                if (empty($row->media['desktop'])) {
                    return '-';
                }

                $url = asset('storage/' . $row->media['desktop']);

                return '<img src="'.$url.'" width="120">';
            })
            ->addColumn('display_duration', function ($row) {
                return $row->display_duration . ' seconds';
            })
            ->rawColumns(['preview', 'display_duration'])
            ->make();
    }

    public function getCreate()
    {
        return view('admin.store_banners.create');
    }

    public function postCreate(Request $request)
    {
        $request->validate([
            'desktop_media' => 'required|file|mimes:jpg,jpeg,png,webp|max:20480',
            'mobile_media'  => 'required|file|mimes:jpg,jpeg,png,webp|max:20480',
            'url' => 'nullable|url',
            'display_duration' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:0,1'
        ]);

        DB::beginTransaction();

        try {

            $desktop = uploadMedia('desktop_media', null, null, 'banners');
            $mobile  = uploadMedia('mobile_media', null, null, 'banners');

            $media = [
                'type'    => 'image',
                'desktop' => $desktop['path'],
                'mobile'  => $mobile['path'],
            ];

            Banner::create([
                'type'       => 'store',
                'media'      => $media,
                'url'        => $request->url,
                'display_duration' => $request->display_duration ?? 3,
                'status'     => (int) $request->input('status', 1),
                'sort_order' => $request->sort_order ?? 0,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Store banner created successfully'
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
        $banner = Banner::where('type','store')->findOrFail($request->id);

        return view('admin.store_banners.update', compact('banner'));
    }

    public function postUpdate(Request $request, $id)
    {
        $banner = Banner::where('type','store')->findOrFail($id);

        $request->validate([
            'desktop_media' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:20480',
            'mobile_media'  => 'nullable|file|mimes:jpg,jpeg,png,webp|max:20480',
            'url' => 'nullable|url',
            'display_duration' => 'nullable|integer|min:1',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'nullable|in:0,1'
        ]);

        DB::beginTransaction();

        try {

            $media = $banner->media ?? [];

            if ($request->hasFile('desktop_media')) {

                $desktop = uploadMedia(
                    'desktop_media',
                    null,
                    null,
                    'banners',
                    $media['desktop'] ?? null
                );

                $media['desktop'] = $desktop['path'];
            }

            if ($request->hasFile('mobile_media')) {

                $mobile = uploadMedia(
                    'mobile_media',
                    null,
                    null,
                    'banners',
                    $media['mobile'] ?? null
                );

                $media['mobile'] = $mobile['path'];
            }

            $media['type'] = 'image';

            $banner->media = $media;

            $banner->url = $request->url;
            $banner->display_duration = $request->display_duration ?? 3;
            $banner->sort_order = $request->sort_order ?? 0;
            $banner->status     = (int) $request->input('status', 1);

            $banner->save();

            DB::commit();

            return response()->json([
                'message' => 'Store banner updated successfully'
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
        $banner = Banner::where('type','store')->findOrFail($request->id);

        if (!empty($banner->media['desktop'])) {

            $path = public_path('storage/' . $banner->media['desktop']);

            if (file_exists($path)) {
                unlink($path);
            }
        }

        if (!empty($banner->media['mobile'])) {

            $path = public_path('storage/' . $banner->media['mobile']);

            if (file_exists($path)) {
                unlink($path);
            }
        }

        $banner->delete();

        return response()->json([
            'message' => 'Store banner deleted successfully'
        ]);
    }

    public function getChangeStatus(Request $request)
    {
        $banner = Banner::where('type','store')->findOrFail($request->id);

        $banner->status = !$banner->status;
        $banner->save();

        return response()->json([
            'message' => 'Status updated successfully'
        ]);
    }
}