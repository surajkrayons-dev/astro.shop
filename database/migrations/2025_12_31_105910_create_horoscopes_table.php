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
        Schema::create('horoscopes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('zodiac_id');

            $table->enum('type', ['today', 'yesterday', 'tomorrow', 'daily', 'weekly', 'monthly', 'yearly']);

            $table->date('date')->nullable(); // daily ke liye

            $table->string('title')->nullable();
            $table->longText('description');

            $table->text('love')->nullable();
            $table->text('career')->nullable();
            $table->text('health')->nullable();
            $table->text('finance')->nullable();

            $table->string('lucky_number')->nullable();
            $table->string('lucky_color')->nullable();

            $table->boolean('status')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('zodiac_id')->references('id')->on('zodiac_signs')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horoscopes');
    }
};
