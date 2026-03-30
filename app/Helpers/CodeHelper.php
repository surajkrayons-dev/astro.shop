<?php

namespace App\Helpers;

use App\Models\User;

class CodeHelper
{
    public static function generateUserCode()
    {
        $last = User::where('type', 'user')->orderBy('id', 'DESC')->first();
        $nextId = $last ? $last->id + 1 : 1;
        return 'USR' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    public static function generateAstroCode()
    {
        $last = User::where('type', 'astro')->orderBy('id', 'DESC')->first();
        $nextId = $last ? $last->id + 1 : 1;
        return 'AST' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }
}
