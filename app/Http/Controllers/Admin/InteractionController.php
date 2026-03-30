<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\User;
use App\Models\ChatSession;
use App\Models\CallSession;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class InteractionController extends Controller
{
    public function getIndex()
    {
        return view('admin.interactions.index');
    }

    public function getList(Request $request)
    {
        /* -------- CHAT QUERY -------- */
        $chatQuery = \DB::table('chat_sessions as cs')
            ->select([
                'cs.id',
                'cs.astrologer_id',
                'cs.user_id',
                \DB::raw("'CHAT' as interaction_type"),
                'cs.created_at as interaction_time',
                'u.name as user_name',
                'u.code as user_code',
                'u.country_code',          // ✅ FIX
                'a.name as astro_name',
                'a.code as astro_code',
            ])
            ->join('users as u', 'u.id', '=', 'cs.user_id')
            ->join('users as a', 'a.id', '=', 'cs.astrologer_id')
            ->where('u.role_id', 3)
            ->where('a.role_id', 2);

        /* -------- CALL QUERY -------- */
        $callQuery = \DB::table('call_sessions as cls')
            ->select([
                'cls.id',
                'cls.astrologer_id',
                'cls.user_id',
                \DB::raw("'CALL' as interaction_type"),
                'cls.created_at as interaction_time',
                'u.name as user_name',
                'u.code as user_code',
                'u.country_code',          // ✅ FIX
                'a.name as astro_name',
                'a.code as astro_code',
            ])
            ->join('users as u', 'u.id', '=', 'cls.user_id')
            ->join('users as a', 'a.id', '=', 'cls.astrologer_id')
            ->where('u.role_id', 3)
            ->where('a.role_id', 2);

        /* -------- COMMON FILTERS -------- */
        foreach ([$chatQuery, $callQuery] as $q) {
            if ($request->astrologer_id) {
                $q->where('astrologer_id', $request->astrologer_id);
            }
            if ($request->user_id) {
                $q->where('user_id', $request->user_id);
            }
            if ($request->filled('country')) {
                $q->where('u.country_code', $request->country); // ✅ MATCHED
            }
        }

        /* -------- TYPE FILTER -------- */
        if ($request->interaction_type === 'CHAT') {
            $finalQuery = $chatQuery;
        } elseif ($request->interaction_type === 'CALL') {
            $finalQuery = $callQuery;
        } else {
            $finalQuery = $chatQuery->unionAll($callQuery);
        }

        return datatables()->of($finalQuery)
            ->addColumn('astro_name', function ($row) {
                return '[ <b>'.e($row->astro_code).'</b> ]<br>'.e($row->astro_name);
            })
            ->addColumn('user_name', function ($row) {
                return '[ <b>'.e($row->user_code).'</b> ]<br>'.e($row->user_name);
            })
            ->addColumn('country', function ($row) {
                $map = $this->countryDialMap();
                return $map[$row->country_code] ?? 'Unknown';
            })
            ->addColumn('interaction_type', fn ($r) => $r->interaction_type)
            ->addColumn('created_at', fn ($r) =>
                date('d M Y h:i A', strtotime($r->interaction_time))
            )
            ->rawColumns(["astro_name", "user_name"])
            ->make(true);
    }

    public function getView(Request $request, $id)
    {
        $type = strtoupper($request->query('type')); // CHAT / CALL

        // Defaults
        $total_chat_duration = 0;
        $total_call_duration = 0;
        $total_chat_amount   = 0;
        $total_call_amount   = 0;

        if ($type === 'CHAT') {

            $session = ChatSession::with(['user', 'astrologer'])->findOrFail($id);

            $user  = $session->user;
            $astro = $session->astrologer;

            $total_chat_duration = (int) ($session->duration ?? 0);
            $total_chat_amount   = (float) ($session->amount ?? 0);
            $status = $session->status ?? null;

        } elseif ($type === 'CALL') {

            $session = CallSession::with(['user', 'astrologer'])->findOrFail($id);

            $user  = $session->user;
            $astro = $session->astrologer;

            $total_call_duration = (int) ($session->duration ?? 0);
            $total_call_amount   = (float) ($session->amount ?? 0);
            $status = $session->status ?? null;

        } else {
            abort(400, 'Interaction type missing');
        }

        $total_spent = $total_chat_amount + $total_call_amount;

        return view(
            'admin.interactions.view',
            compact(
                'user',
                'astro',
                'total_chat_duration',
                'total_call_duration',
                'total_chat_amount',
                'total_call_amount',
                'total_spent',
                'status'
            )
        );
    }

    private function countryDialMap()
    {
        return Cache::rememberForever('country_dial_map', function () {

            $response = Http::get(
                'https://restcountries.com/v3.1/all?fields=name,idd'
            );

            $map = [];

            if ($response->successful()) {
                foreach ($response->json() as $country) {
                    if (
                        isset($country['idd']['root']) &&
                        isset($country['idd']['suffixes'])
                    ) {
                        foreach ($country['idd']['suffixes'] as $suffix) {
                            $code = $country['idd']['root'] . $suffix; // +91
                            $map[$code] = $country['name']['common']; // India
                        }
                    }
                }
            }

            return $map;
        });
    }
}