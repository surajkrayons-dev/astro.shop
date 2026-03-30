<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends AdminController
{
    public function getIndex(Request $request)
    {
        return view('admin.dashboard.index');
    }

    /**
     * TOP STATS
     */
    public function getStats(Request $request)
    {
        $today = now()->toDateString();

        return response()->json([
            'total_astrologers' => User::where('type', 'astro')->count(),
            'total_users'       => User::where('type', 'user')->count(),

            'online_astrologers' => User::where('type', 'astro')
                ->where('is_online', 1)
                ->whereDate('updated_at', $today)
                ->count(),

            'online_users' => User::where('type', 'user')
                ->where('is_online', 1)
                ->whereDate('updated_at', $today)
                ->count(),

            'active_call_connections' => DB::table('call_sessions')
                ->where('status', 'active')
                ->whereDate('created_at', $today)
                ->count(),

            'active_chat_connections' => DB::table('chat_sessions')
                ->where('status', 'active')
                ->whereDate('created_at', $today)
                ->count(),
        ]);
    }


    /**
     * GRAPH 1 – PLATFORM GROWTH
     */
    public function getGrowthGraph(Request $request)
    {
        $start = Carbon::parse($request->start_date)->startOfDay()->toDateString();
        $end   = Carbon::parse($request->end_date)->endOfDay()->toDateString();

        $dates = $this->getDaysBetweenDates($start, $end);

        $astro = User::selectRaw("COUNT(id) as total, DATE(created_at) as date")
            ->where('type', 'astro')
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $users = User::selectRaw("COUNT(id) as total, DATE(created_at) as date")
            ->where('type', 'user')
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $labels = $astrologers = $customers = [];

        foreach ($dates['dates'] as $date) {
            $normalized = Carbon::parse($date)->toDateString();

            $labels[]      = $normalized;
            $astrologers[] = $astro[$normalized] ?? 0;
            $customers[]   = $users[$normalized] ?? 0;
        }

        return response()->json(compact('labels', 'astrologers', 'customers'));
    }

    /**
     * GRAPH 2 – ENGAGEMENT
     */
    public function getEngagementGraph(Request $request)
    {
        $start = Carbon::parse($request->start_date)->startOfDay()->toDateString();
        $end   = Carbon::parse($request->end_date)->endOfDay()->toDateString();

        $dates = $this->getDaysBetweenDates($start, $end);

        $calls = DB::table('call_sessions')
            ->selectRaw("COUNT(id) as total, DATE(created_at) as date")
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $chats = DB::table('chat_sessions')
            ->selectRaw("COUNT(id) as total, DATE(created_at) as date")
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $labels = $callData = $chatData = [];

        foreach ($dates['dates'] as $date) {
            $normalized = Carbon::parse($date)->toDateString();
            $labels[]   = $normalized;
            $callData[] = $calls[$normalized] ?? 0;
            $chatData[] = $chats[$normalized] ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'calls'  => $callData,
            'chats'  => $chatData,
        ]);
    }


    function getDaysBetweenDates($start, $end)
    {
        $start = Carbon::parse($start);
        $end   = Carbon::parse($end);
        $dates = [];

        for ($date = $start; $date->lte($end); $date->addDay()) {
            $dates[] = $date->toDateString(); // YYYY-MM-DD
        }

        return ['dates' => $dates];
    }
}