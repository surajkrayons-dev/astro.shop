<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserService extends Model
{
    use HasFactory;

    protected $table = 'user_services';

    protected $fillable = ['client_id', 'services', 'service_cost'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}