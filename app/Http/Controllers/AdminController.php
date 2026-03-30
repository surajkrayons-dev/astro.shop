<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends BaseController
{
    public function __construct()
    {
        // sleep(10);
        $this->middleware(function ($request, $next) {
            \View::share('admin', auth()->user());
            \View::share('app_settings', getAppSettings());

            return $next($request);
        });
    }
}
