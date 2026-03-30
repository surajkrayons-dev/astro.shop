<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPaymentAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_payment_accounts';

    protected $fillable = [
        'user_id',
        'type',
        'account_holder_name',
        'upi_id',
        'bank_name',
        'account_number',
        'ifsc_code',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(PayoutRequest::class, 'payment_account_id');
    }
}
