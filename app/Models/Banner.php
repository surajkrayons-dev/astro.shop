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
        'status',
        'sort_order'
    ];

    protected $casts = [
        'media' => 'array',
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];
}
