<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlternativeAddress extends Model
{
    use HasFactory;

    protected $table = 'alternative_addresses';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'country_code',
        'mobile',
        'alternative_mobile',
        'city',
        'state',
        'country',
        'address',
        'pincode'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}