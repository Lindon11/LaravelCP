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
        if (!Schema::hasTable('crime_location_attempts')) Schema::create('crime_location_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('crime_location_id')->constrained()->onDelete('cascade');
            $table->boolean('success')->default(false);
            $table->boolean('jailed')->default(false);
            $table->integer('cash_earned')->default(0);
            $table->integer('exp_earned')->default(0);
            $table->integer('respect_earned')->default(0);
            $table->text('result_message')->nullable();
            $table->timestamp('attempted_at')->useCurrent();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'crime_location_id']);
            $table->index('attempted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crime_location_attempts');
    }
};
