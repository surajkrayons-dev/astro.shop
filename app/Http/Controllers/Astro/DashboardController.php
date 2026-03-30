<?php

namespace App\Http\Controllers\Astro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getIndex(Request $request)
    {
        return view('astro.dashboard.index');
    }

    public function getStats(Request $request)
    {
        $astro = auth()->user();
        $today = now()->toDateString();

        // Earnings
        $chatEarnings = \DB::table('chat_sessions')
            ->where('astrologer_id', $astro->id)
            ->whereDate('created_at', $today)
            ->where('status', 'ended')
            ->sum('total_amount');

        $callEarnings = \DB::table('call_sessions')
            ->where('astrologer_id', $astro->id)
            ->whereDate('created_at', $today)
            ->where('status', 'ended')
            ->sum('total_amount');

        // Counts
        $todayChats = \DB::table('chat_sessions')
            ->where('astrologer_id', $astro->id)
            ->whereDate('created_at', $today)
            ->count();

        $todayCalls = \DB::table('call_sessions')
            ->where('astrologer_id', $astro->id)
            ->whereDate('created_at', $today)
            ->count();

        // Durations (minutes)
        $chatDuration = \DB::table('chat_sessions')
            ->where('astrologer_id', $astro->id)
            ->whereDate('created_at', $today)
            ->sum('duration_minutes');

        $callDuration = \DB::table('call_sessions')
            ->where('astrologer_id', $astro->id)
            ->whereDate('created_at', $today)
            ->sum('duration_minutes');

        return response()->json([
            'today_earnings' => number_format($chatEarnings + $callEarnings, 2),
            'today_chats'    => $todayChats,
            'today_calls'    => $todayCalls,
            'total_sessions' => $todayChats + $todayCalls,
            'chat_duration'  => (int) $chatDuration,
            'call_duration'  => (int) $callDuration,

            // ✅ STATUS BASED ACTIVE / INACTIVE
            'active_status'  => $astro->status == 1 ? 'Active' : 'Inactive',

            'wallet_balance' => number_format(optional($astro->wallet)->balance ?? 0, 2),
        ]);
    }

    public function getOrdersGraph(Request $request)
    {
        $user = auth()->user();

        $labels = [];
        $orders = [];
        $earnings = [];

        $date_range = getDaysBetweenDates($request->start_date, $request->end_date);

        $result = \App\Models\Order::select(\DB::raw("COUNT(id) AS orders, SUM(total_amount) AS earnings, DATE_FORMAT(created_at, '{$date_range['sql_date_format']}') AS label"))
            ->whereBetween(\DB::raw('DATE(created_at)'), [$request->start_date, $request->end_date])
            ->whereStatus('completed')
            ->when($user->isStaff(), fn($builder) => $builder->whereCreatedBy($user->id))
            ->groupBy(\DB::raw("DATE_FORMAT(created_at, '{$date_range['sql_date_format']}')"))
            ->get();

        foreach ($date_range['dates'] as $date) {
            $order = $result->isNotEmpty() ? $result->first(fn($row) => $row->label == $date) : null;

            $labels[] = $date;
            $orders[] = $order->orders ?? 0;
            $earnings[] = $order->earnings ?? 0;
        }

        return response()->json(compact('labels', 'orders', 'earnings'));
    }

    public function getLoginRequested(Request $request)
    {
        return view('astro.dashboard.login_request');
    }

    public function getLoginRequestStatus(Request $request)
    {
        $login_request_hash = $request->session()->get('login_request_hash');
        $status = \App\Models\LoginRequest::where('hash_token', $login_request_hash)->value('status');

        return response()->json(compact('status'));
    }
}