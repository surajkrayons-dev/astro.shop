<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayoutRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payout_requests';

    protected $fillable = [
        'user_id',
        'wallet_id',
        'payment_account_id',
        'amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function paymentAccount()
    {
        return $this->belongsTo(UserPaymentAccount::class, 'payment_account_id');
    }
}
