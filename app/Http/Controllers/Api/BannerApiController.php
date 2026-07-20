<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerApiController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type' => 'required|in:astro,store'
        ]);

        $banners = Banner::where('type', $request->type)
            ->where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function ($banner) {

                return [
                    'id'               => $banner->id,
                    'media_type'       => $banner->media['type'] ?? 'image',

                    'desktop_media'    => !empty($banner->media['desktop'])
                        ? asset('storage/' . $banner->media['desktop'])
                        : null,

                    'mobile_media'     => !empty($banner->media['mobile'])
                        ? asset('storage/' . $banner->media['mobile'])
                        : null,

                    'url'              => $banner->url,
                    'display_duration' => $banner->display_duration,
                    'sort_order'       => $banner->sort_order,
                ];
            });

        return response()->json([
            'success' => true,
            'type'    => $request->type,
            'count'   => $banners->count(),
            'data'    => $banners
        ]);
    }
}