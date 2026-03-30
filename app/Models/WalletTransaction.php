<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'wallet_transactions';

    protected $fillable = [
        'wallet_id',
        'type',
        'direction',
        'amount',
        'balance_before',
        'balance_after',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public static function createEntry(
        Wallet $wallet,
        string $type,
        string $direction,
        float $amount,
        ?int $referenceId = null
    ) {
        $balanceBefore = $wallet->balance;

        $balanceAfter = $direction === 'debit'
            ? $balanceBefore - $amount
            : $balanceBefore + $amount;

        return self::create([
            'wallet_id'      => $wallet->id,
            'type'           => $type,
            'direction'      => $direction,
            'amount'         => $amount,
            'balance_before' => $balanceBefore,
            'balance_after'  => $balanceAfter,
            'reference_id'   => $referenceId,
        ]);
    }
}
