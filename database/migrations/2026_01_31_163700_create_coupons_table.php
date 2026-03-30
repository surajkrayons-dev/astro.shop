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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('discount_type', ['flat','percentage']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_amount', 10, 2)->nullable(); // minimum cart/order amount
            $table->decimal('max_discount', 10, 2)->nullable(); // max discount cap
            $table->date('expiry_date');
            $table->boolean('status')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
