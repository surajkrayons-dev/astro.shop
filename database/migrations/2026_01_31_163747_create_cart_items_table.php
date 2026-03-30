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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('product_id');

            $table->integer('quantity');
            $table->decimal('ratti', 10, 2)->nullable();

            $table->decimal('price_at_time', 12, 2);
            $table->decimal('total_price', 12, 2);

            $table->timestamps();

            $table->foreign('cart_id')
                ->references('id')->on('carts')
                ->cascadeOnDelete();
                
            $table->foreign('product_id')
                ->references('id')->on('products')
                ->cascadeOnDelete();

            $table->unique(['cart_id', 'product_id', 'ratti']);
            $table->index('cart_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
