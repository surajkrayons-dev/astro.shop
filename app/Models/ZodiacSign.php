<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZodiacSign extends Model
{
    use HasFactory;

    protected $table = 'zodiac_signs';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'status',
    ];

    public function horoscopes()
    {
        return $this->hasMany(Horoscope::class, 'zodiac_id');
    }
}
