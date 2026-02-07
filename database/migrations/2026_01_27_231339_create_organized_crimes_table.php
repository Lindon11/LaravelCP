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
        if (!Schema::hasTable('organized_crimes')) Schema::create('organized_crimes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('success_rate')->default(50);
            $table->decimal('min_reward', 15, 2);
            $table->decimal('max_reward', 15, 2);
            $table->integer('required_members')->default(3);
            $table->integer('required_level')->default(1);
            $table->integer('cooldown')->default(3600); // 1 hour
            $table->timestamps();
        });

        if (!Schema::hasTable('organized_crime_attempts')) Schema::create('organized_crime_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organized_crime_id')->constrained('organized_crimes')->onDelete('cascade');
            $table->foreignId('gang_id')->constrained('gangs')->onDelete('cascade');
            $table->foreignId('leader_id')->constrained('users')->onDelete('cascade');
            $table->boolean('success');
            $table->decimal('reward_earned', 15, 2)->default(0);
            $table->json('participants');
            $table->text('result_message');
            $table->timestamp('attempted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organized_crimes');
    }
};
