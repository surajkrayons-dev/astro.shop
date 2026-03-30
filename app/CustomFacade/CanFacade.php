<?php

namespace App\CustomFacade;

use Illuminate\Support\Facades\Facade;

class CanFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'can';
    }
}
