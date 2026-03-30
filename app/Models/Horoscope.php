<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horoscope extends Model
{
    use HasFactory;

    protected $table = 'horoscopes';

    protected $fillable = [
        'zodiac_id',
        'type',
        'date',
        'title',
        'description',
        'love',
        'career',
        'health',
        'finance',
        'lucky_number',
        'lucky_color',
        'status',
        'created_by',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function zodiac()
    {
        return $this->belongsTo(ZodiacSign::class, 'zodiac_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
