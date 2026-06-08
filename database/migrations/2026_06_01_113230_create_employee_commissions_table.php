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

            $table->enum(
                'status',
                ['delivery_pending', 'pending', 'paid', 'cancelled']
            )->default('delivery_pending');

            // Withdraw Flow
            $table->boolean('is_withdraw_requested')->default(false);
            $table->timestamp('withdraw_requested_at')->nullable();

            // Payment Audit
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();

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

            $table->foreign('paid_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('employee_id');
            $table->index('order_id');
            $table->index('coupon_id');
            $table->index('status');
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
