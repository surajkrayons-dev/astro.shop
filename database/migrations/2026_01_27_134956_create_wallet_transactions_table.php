<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('wallet_id');

            $table->enum('type', [
                'recharge',
                'call_debit',
                'chat_debit',
                'call_credit',
                'chat_credit',
                'refund',
                'withdraw'
            ]);

            $table->enum('direction', ['credit', 'debit']);

            $table->decimal('amount', 12, 2);

            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);

            $table->unsignedBigInteger('reference_id')->nullable(); 
            // call_id / chat_id / recharge_id etc

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('wallet_id')
                ->references('id')
                ->on('wallets')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};