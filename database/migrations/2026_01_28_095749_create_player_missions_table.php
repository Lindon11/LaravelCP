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
        if (!Schema::hasTable('player_missions')) Schema::create('player_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('mission_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['available', 'in_progress', 'completed'])->default('available');
            $table->integer('progress')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('available_again_at')->nullable(); // For repeatable missions
            $table->timestamps();

            $table->unique(['user_id', 'mission_id']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_missions');
    }
};
