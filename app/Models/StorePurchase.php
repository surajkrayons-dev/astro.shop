<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorePurchase extends Model
{
    use HasFactory;

    protected $table = 'store_purchases';

    protected $fillable = [
        'order_id',
        'transaction_id',
        'method',
        'amount',
        'status'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
