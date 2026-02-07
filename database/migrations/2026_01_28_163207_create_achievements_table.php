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
        if (!Schema::hasTable('achievements')) Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('type'); // crime_count, level_reached, cash_earned, kills, properties_owned, etc
            $table->integer('requirement'); // Number needed to unlock
            $table->integer('reward_cash')->default(0);
            $table->integer('reward_xp')->default(0);
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        
        // User achievements pivot table
        if (!Schema::hasTable('user_achievements')) Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained()->onDelete('cascade');
            $table->integer('progress')->default(0);
            $table->timestamp('earned_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'achievement_id']);
            $table->index(['user_id', 'earned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
    }
};
