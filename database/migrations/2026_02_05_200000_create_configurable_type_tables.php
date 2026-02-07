<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Item Rarities
        if (!Schema::hasTable('item_rarities')) Schema::create('item_rarities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Property Types
        if (!Schema::hasTable('property_types')) Schema::create('property_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Announcement Types
        if (!Schema::hasTable('announcement_types')) Schema::create('announcement_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Crime Difficulties
        if (!Schema::hasTable('crime_difficulties')) Schema::create('crime_difficulties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Casino Game Types
        if (!Schema::hasTable('casino_game_types')) Schema::create('casino_game_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Company Industries
        if (!Schema::hasTable('company_industries')) Schema::create('company_industries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Stock Sectors
        if (!Schema::hasTable('stock_sectors')) Schema::create('stock_sectors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Course Skills
        if (!Schema::hasTable('course_skills')) Schema::create('course_skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Course Difficulties
        if (!Schema::hasTable('course_difficulties')) Schema::create('course_difficulties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Achievement Stats
        if (!Schema::hasTable('achievement_stats')) Schema::create('achievement_stats', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Mission Frequencies
        if (!Schema::hasTable('mission_frequencies')) Schema::create('mission_frequencies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Mission Objective Types
        if (!Schema::hasTable('mission_objective_types')) Schema::create('mission_objective_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Bounty Statuses
        if (!Schema::hasTable('bounty_statuses')) Schema::create('bounty_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Lottery Statuses
        if (!Schema::hasTable('lottery_statuses')) Schema::create('lottery_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Item Effect Types
        if (!Schema::hasTable('item_effect_types')) Schema::create('item_effect_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Item Modifier Types
        if (!Schema::hasTable('item_modifier_types')) Schema::create('item_modifier_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_modifier_types');
        Schema::dropIfExists('item_effect_types');
        Schema::dropIfExists('lottery_statuses');
        Schema::dropIfExists('bounty_statuses');
        Schema::dropIfExists('mission_objective_types');
        Schema::dropIfExists('mission_frequencies');
        Schema::dropIfExists('achievement_stats');
        Schema::dropIfExists('course_difficulties');
        Schema::dropIfExists('course_skills');
        Schema::dropIfExists('stock_sectors');
        Schema::dropIfExists('company_industries');
        Schema::dropIfExists('casino_game_types');
        Schema::dropIfExists('crime_difficulties');
        Schema::dropIfExists('announcement_types');
        Schema::dropIfExists('property_types');
        Schema::dropIfExists('item_rarities');
    }
};
