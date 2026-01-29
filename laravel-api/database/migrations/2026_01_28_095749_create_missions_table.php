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
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->enum('type', ['story', 'daily', 'repeatable', 'one_time'])->default('one_time');
            $table->integer('required_level')->default(1);
            $table->foreignId('required_location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->string('objective_type'); // crime, combat, travel, purchase, etc
            $table->integer('objective_count')->default(1);
            $table->json('objective_data')->nullable(); // Additional data like crime_id, item_id, etc
            $table->integer('cash_reward')->default(0);
            $table->integer('respect_reward')->default(0);
            $table->integer('experience_reward')->default(0);
            $table->integer('item_reward_id')->nullable();
            $table->integer('item_reward_quantity')->default(1);
            $table->integer('cooldown_hours')->default(0); // For repeatable missions
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index('required_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
