<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'hash_token',
        'created_by',
        'modified_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
