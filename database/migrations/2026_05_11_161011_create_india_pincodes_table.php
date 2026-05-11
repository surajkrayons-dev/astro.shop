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
        Schema::create('india_pincodes', function (Blueprint $table) {

            $table->id();

            $table->string('circle_name')->nullable();
            $table->string('region_name')->nullable();
            $table->string('division_name')->nullable();
            $table->string('office_name')->nullable();

            $table->string('pincode', 10)->index();

            $table->string('office_type', 20)->nullable();

            $table->string('district')->nullable()->index();
            $table->string('state')->nullable()->index();

            $table->string('state_code', 10)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('india_pincodes');
    }
};
