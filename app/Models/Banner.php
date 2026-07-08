<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $table = 'banners';

    protected $fillable = [
        'type',
        'media',
        'url',
        'display_duration',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'media' => 'array',
        'display_duration' => 'integer',
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];
}
