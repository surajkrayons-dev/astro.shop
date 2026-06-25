<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_slug',
        'product_image',
        'ratti',     
        'quantity',
        'price',
        'total',
        'weight',
        'length',
        'breadth',
        'height',
        'gst_rate',
        'gst_amount',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'tax_type',
        'hsn_code',
    ];

    protected $casts = [
        'ratti' => 'float',
        'quantity' => 'integer',

        'price' => 'float',
        'total' => 'float',

        'weight' => 'float',
        'length' => 'float',
        'breadth' => 'float',
        'height' => 'float',
        'gst_rate' => 'float',
        'gst_amount' => 'float',
        'taxable_amount' => 'float',
        'cgst_amount' => 'float',
        'sgst_amount' => 'float',
        'igst_amount' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function returnRequest()
    {
        return $this->hasOne(ReturnRequest::class);
    }

    public function cancellations()
    {
        return $this->hasMany(OrderItemCancellation::class);
    }
}