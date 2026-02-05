<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Item Rarities
        Schema::create('item_rarities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Property Types
        Schema::create('property_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Announcement Types
        Schema::create('announcement_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Crime Difficulties
        Schema::create('crime_difficulties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Casino Game Types
        Schema::create('casino_game_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Company Industries
        Schema::create('company_industries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Stock Sectors
        Schema::create('stock_sectors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Course Skills
        Schema::create('course_skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Course Difficulties
        Schema::create('course_difficulties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Achievement Stats
        Schema::create('achievement_stats', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Mission Frequencies
        Schema::create('mission_frequencies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Mission Objective Types
        Schema::create('mission_objective_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Bounty Statuses
        Schema::create('bounty_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Lottery Statuses
        Schema::create('lottery_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Item Effect Types
        Schema::create('item_effect_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Item Modifier Types
        Schema::create('item_modifier_types', function (Blueprint $table) {
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
