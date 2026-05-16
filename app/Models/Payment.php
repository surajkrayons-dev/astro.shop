<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];
    
    protected $table = 'payments';

    protected $fillable = [
        'user_id',
        'platform',
        'order_id',
        'payment_gateway',
        'transaction_id',
        'amount',
        'currency',
        'payment_status',
        'payment_mode',
        'customer_email',
        'customer_phone',
        'payment_request_data',
        'payment_response_data',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_request_data' => 'array',
        'payment_response_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function walletRecharge()
    {
        return $this->hasOne(WalletRecharge::class);
    }
}