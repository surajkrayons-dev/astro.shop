<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class HoroscopeGenerateController extends Controller
{
    private function getToken()
    {
        return Cache::remember('prokerala_token', 3000, function () {

            $token = Http::asForm()->post('https://api.prokerala.com/token', [
                'grant_type'    => 'client_credentials',
                'client_id'     => env('PROKERALA_CLIENT_ID'),
                'client_secret' => env('PROKERALA_CLIENT_SECRET'),
            ]);

            return $token->successful() ? $token['access_token'] : null;
        });
    }

    private function getCoordinates($place)
    {
        $geo = Http::withHeaders(['User-Agent' => 'astro-app'])
            ->get("https://nominatim.openstreetmap.org/search", [
                'q' => $place,
                'format' => 'json',
                'limit' => 1
            ]);

        if (!isset($geo[0])) return null;

        return [
            'latitude' => (float)$geo[0]['lat'],
            'longitude' => (float)$geo[0]['lon'],
        ];
    }

    private function getSunSign(string $date): string
    {
        $day = (int) date('d', strtotime($date));
        $month = (int) date('m', strtotime($date));

        return match (true) {
            ($month == 3 && $day >= 21) || ($month == 4 && $day <= 19) => 'aries',
            ($month == 4 && $day >= 20) || ($month == 5 && $day <= 20) => 'taurus',
            ($month == 5 && $day >= 21) || ($month == 6 && $day <= 20) => 'gemini',
            ($month == 6 && $day >= 21) || ($month == 7 && $day <= 22) => 'cancer',
            ($month == 7 && $day >= 23) || ($month == 8 && $day <= 22) => 'leo',
            ($month == 8 && $day >= 23) || ($month == 9 && $day <= 22) => 'virgo',
            ($month == 9 && $day >= 23) || ($month == 10 && $day <= 22) => 'libra',
            ($month == 10 && $day >= 23) || ($month == 11 && $day <= 21) => 'scorpio',
            ($month == 11 && $day >= 22) || ($month == 12 && $day <= 21) => 'sagittarius',
            ($month == 12 && $day >= 22) || ($month == 1 && $day <= 19) => 'capricorn',
            ($month == 1 && $day >= 20) || ($month == 2 && $day <= 18) => 'aquarius',
            default => 'pisces',
        };
    }

    // public function fullReport(Request $request)
    // {
    //     $request->validate([
    //         'name'        => 'required|string',
    //         'gender'      => 'required|string',
    //         'birth_date'  => 'required|date',
    //         'birth_time'  => 'required',
    //         'birth_place' => 'required|string'
    //     ]);

    //     $token = $this->getToken();
    //     if (!$token) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Token generation failed. Check credentials.'
    //         ], 500);
    //     }

    //     // Place → Latitude/Longitude (Mandatory)
    //     $coordinates = $this->getCoordinates($request->birth_place);
    //     if (!$coordinates) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Invalid place. Please provide proper city name.'
    //         ], 400);
    //     }

    //     $datetime = $request->birth_date . 'T' . $request->birth_time . ':00+05:30';

    //     $results = [
    //         'user' => [
    //             'name' => $request->name,
    //             'birth_place' => $request->birth_place,
    //             'latitude' => $coordinates['latitude'],
    //             'longitude' => $coordinates['longitude']
    //         ]
    //     ];

    //     $sign = $this->getSunSign($request->birth_date);

    //     $daily = Http::withToken($token)
    //         ->get('https://api.prokerala.com/v2/horoscope/daily', [
    //             'sign'     => $sign,
    //             'datetime' => now()->format('Y-m-d\TH:i:sP')
    //         ]);

    //     $results['daily_horoscope'] = [
    //         'http_status' => $daily->status(),
    //         'success'     => $daily->successful(),
    //         'response'    => $daily->json()
    //     ];

    //     $planets = Http::withToken($token)
    //         ->get('https://api.prokerala.com/v2/astrology/planet-position', [
    //             'datetime'    => $datetime,
    //             'coordinates' => $coordinates['latitude'] . ',' . $coordinates['longitude'],
    //             'ayanamsa'    => 1
    //         ]);

    //     $results['planet_positions'] = [
    //         'http_status' => $planets->status(),
    //         'success'     => $planets->successful(),
    //         'response'    => $planets->json()
    //     ];

    //     $birthChart = Http::withToken($token)
    //         ->post('https://api.prokerala.com/v2/astrology/birth-chart', [
    //             "datetime" => $datetime,
    //             "coordinates" => [
    //                 "latitude"  => $coordinates['latitude'],
    //                 "longitude" => $coordinates['longitude']
    //             ],
    //             "settings" => [
    //                 "ayanamsa" => 1
    //             ]
    //         ]);

    //     $results['birth_chart'] = [
    //         'http_status' => $birthChart->status(),
    //         'success'     => $birthChart->successful(),
    //         'response'    => $birthChart->json()
    //     ];

    //     return response()->json($results);
    // }
    public function fullReport(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'gender'      => 'required|string',
            'birth_date'  => 'required|date',
            'birth_time'  => 'required',
            'birth_place' => 'required|string'
        ]);

        $token = $this->getToken();
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token generation failed'
            ], 500);
        }

        $datetime = $request->birth_date . 'T' . $request->birth_time . ':00+05:30';

        $results = [];

        $sign = $this->getSunSign($request->birth_date);

        $daily = Http::withToken($token)
            ->get('https://api.prokerala.com/v2/horoscope/daily', [
                'sign'     => $sign,
                'datetime' => now()->format('Y-m-d\TH:i:sP')
            ]);

        $results['daily_horoscope'] = [
            'http_status' => $daily->status(),
            'success'     => $daily->successful(),
            'response'    => $daily->json()
        ];

        $planets = Http::withToken($token)
            ->get('https://api.prokerala.com/v2/astrology/planet-position', [
                'datetime' => $datetime
            ]);

        $results['planet_positions'] = [
            'http_status' => $planets->status(),
            'success'     => $planets->successful(),
            'response'    => $planets->json()
        ];

        $birthChart = Http::withToken($token)
            ->post('https://api.prokerala.com/v2/astrology/birth-chart', [
                "datetime" => $datetime
            ]);

        $results['birth_chart'] = [
            'http_status' => $birthChart->status(),
            'success'     => $birthChart->successful(),
            'response'    => $birthChart->json()
        ];

        return response()->json($results);
    }
}