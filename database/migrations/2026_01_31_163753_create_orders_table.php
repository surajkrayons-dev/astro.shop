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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();

            $table->string('order_number')->unique();

            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('delivery_charge', 10, 2)->default(0);

            $table->decimal('wallet_used', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);

            $table->decimal('total_amount', 10, 2); // after discount

            $table->enum('status', [
                'pending',
                'paid',
                'packed',
                'shipped',
                'delivered',
                'cancelled'
            ])->default('pending');

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->json('price_breakdown')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('coupon_id')
                ->references('id')->on('coupons')
                ->nullOnDelete();

            $table->foreign('payment_id')
                ->references('id')->on('payments')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
