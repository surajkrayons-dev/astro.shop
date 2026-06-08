<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCommission extends Model
{
    use HasFactory;

    protected $table = 'employee_commissions';

    protected $fillable = [
        'employee_id',
        'order_id',
        'coupon_id',
        'order_amount',
        'commission_percentage',
        'commission_amount',
        'status',
        'is_withdraw_requested',
        'withdraw_requested_at',
        'paid_at',
        'paid_by',
    ];

    protected $casts = [
        'is_withdraw_requested' => 'boolean',
        'withdraw_requested_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class,'employee_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
