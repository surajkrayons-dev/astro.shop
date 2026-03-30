<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'order_item_id',
        'reason',
        'status',
        'payment_account_id'
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function paymentAccount()
    {
        return $this->belongsTo(UserPaymentAccount::class, 'payment_account_id');
    }
}