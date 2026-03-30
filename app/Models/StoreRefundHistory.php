<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreRefundHistory extends Model
{
    use HasFactory;

    protected $table = 'store_refund_histories';

    protected $fillable = [
        'user_id',
        'order_id',
        'order_item_id',
        'product_id',
        'quantity',
        'amount',
        'picked_at',
        'refunded_at',
        'refund_method',
        'refund_reference',
    ];

    protected $casts = [
        'picked_at'   => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
