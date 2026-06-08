<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeWithdrawRequest extends Model
{
    use HasFactory;

    protected $table = 'employee_withdraw_requests';

    protected $fillable = [
        'employee_id',
        'amount',
        'status',
        'remarks',
        'requested_at',
        'processed_at',
        'processed_by',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}