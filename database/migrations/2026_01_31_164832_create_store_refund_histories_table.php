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
        Schema::create('store_refund_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('product_id');

            $table->integer('quantity');
            $table->decimal('amount', 10, 2);

            $table->timestamp('picked_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->string('refund_method'); 
            // upi | bank | cod

            $table->string('refund_reference')->nullable(); 
            // txn id / utr / manual ref

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();
                
            $table->foreign('order_id')
                ->references('id')->on('orders')
                ->cascadeOnDelete();
                
            $table->foreign('order_item_id')
                ->references('id')->on('order_items')
                ->cascadeOnDelete();
                
            $table->foreign('product_id')
                ->references('id')->on('products')
                ->cascadeOnDelete();
                
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_refund_histories');
    }
};
