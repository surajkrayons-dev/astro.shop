<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AstrologerAvailability extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'astrologer_availabilities';

    protected $fillable = [
        'user_id',
        'from_time',
        'to_time',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
