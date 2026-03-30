<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('payment_account_id')->nullable();
            $table->string('reason');
            $table->enum('status', ['requested','approved','picked','rejected','refunded'])->default('requested');
            $table->timestamps();

            $table->foreign('order_item_id')
                ->references('id')->on('order_items')
                ->cascadeOnDelete();

            $table->foreign('payment_account_id')
                ->references('id')->on('user_payment_accounts')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
