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
        Schema::create('employee_commissions', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('coupon_id');

            $table->decimal('order_amount', 10, 2);

            $table->decimal('commission_percentage', 5, 2);

            $table->decimal('commission_amount', 10, 2);

            $table->enum('status', [
                'pending',
                'paid'
            ])->default('pending');

            $table->timestamps();

            $table->foreign('employee_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();

            $table->foreign('coupon_id')
                ->references('id')
                ->on('coupons')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_commissions');
    }
};
