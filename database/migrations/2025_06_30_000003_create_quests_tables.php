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
        Schema::create('quests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category'); // main, side, daily, weekly, tutorial
            $table->string('status')->default('active'); // active, inactive, archived
            $table->integer('level_requirement')->default(1);
            $table->json('prerequisites')->nullable(); // quest IDs that must be completed first
            $table->json('objectives')->nullable();
            $table->json('rewards')->nullable();
            $table->integer('experience_reward')->default(0);
            $table->integer('money_reward')->default(0);
            $table->integer('time_limit')->nullable(); // in minutes
            $table->boolean('is_repeatable')->default(false);
            $table->integer('cooldown')->nullable(); // in minutes for repeatable quests
            $table->integer('story_order')->nullable(); // for main story quests
            $table->timestamps();

            $table->index(['category', 'status']);
            $table->index('level_requirement');
        });

        Schema::create('player_quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('active'); // active, completed, failed, abandoned
            $table->json('progress')->nullable(); // current objective progress
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('times_completed')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['quest_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_quests');
        Schema::dropIfExists('quests');
    }
};
