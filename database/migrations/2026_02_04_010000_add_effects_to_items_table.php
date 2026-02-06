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
        Schema::table('items', function (Blueprint $table) {
            $table->json('effects')->nullable()->after('stats');
            $table->integer('cooldown')->default(0)->after('effects'); // Cooldown in seconds for consumables
            $table->integer('duration')->default(0)->after('cooldown'); // Effect duration in seconds (0 = instant)
            $table->boolean('is_usable')->default(false)->after('duration'); // Can this item be "used"?
        });

        // Create item_effects table for predefined effect types
        Schema::create('item_effects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('type'); // buff, debuff, instant, passive
            $table->string('stat')->nullable(); // health, energy, strength, defense, speed, etc.
            $table->string('modifier_type')->default('flat'); // flat, percent
            $table->timestamps();
        });

        // Insert common effect types
        \DB::table('item_effects')->insert([
            ['name' => 'heal', 'display_name' => 'Heal', 'description' => 'Restores health', 'type' => 'instant', 'stat' => 'health', 'modifier_type' => 'flat', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'heal_percent', 'display_name' => 'Heal %', 'description' => 'Restores health by percentage', 'type' => 'instant', 'stat' => 'health', 'modifier_type' => 'percent', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'restore_energy', 'display_name' => 'Restore Energy', 'description' => 'Restores energy', 'type' => 'instant', 'stat' => 'energy', 'modifier_type' => 'flat', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'boost_strength', 'display_name' => 'Strength Boost', 'description' => 'Temporarily increases strength', 'type' => 'buff', 'stat' => 'strength', 'modifier_type' => 'flat', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'boost_defense', 'display_name' => 'Defense Boost', 'description' => 'Temporarily increases defense', 'type' => 'buff', 'stat' => 'defense', 'modifier_type' => 'flat', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'boost_speed', 'display_name' => 'Speed Boost', 'description' => 'Temporarily increases speed', 'type' => 'buff', 'stat' => 'speed', 'modifier_type' => 'flat', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'boost_damage', 'display_name' => 'Damage Boost', 'description' => 'Temporarily increases damage', 'type' => 'buff', 'stat' => 'damage', 'modifier_type' => 'percent', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'reduce_cooldown', 'display_name' => 'Cooldown Reduction', 'description' => 'Reduces crime/action cooldowns', 'type' => 'buff', 'stat' => 'cooldown', 'modifier_type' => 'percent', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'experience_boost', 'display_name' => 'XP Boost', 'description' => 'Increases experience gained', 'type' => 'buff', 'stat' => 'experience', 'modifier_type' => 'percent', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'money_boost', 'display_name' => 'Money Boost', 'description' => 'Increases money earned', 'type' => 'buff', 'stat' => 'money', 'modifier_type' => 'percent', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'crime_success', 'display_name' => 'Crime Success Boost', 'description' => 'Increases crime success rate', 'type' => 'buff', 'stat' => 'crime_success', 'modifier_type' => 'percent', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'jail_reduction', 'display_name' => 'Jail Time Reduction', 'description' => 'Reduces jail time', 'type' => 'instant', 'stat' => 'jail_time', 'modifier_type' => 'percent', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'revive', 'display_name' => 'Revive', 'description' => 'Revives from hospital', 'type' => 'instant', 'stat' => 'hospital', 'modifier_type' => 'flat', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Create active_effects table to track player active buffs/debuffs
        Schema::create('player_active_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('effect_name');
            $table->string('stat');
            $table->decimal('value', 10, 2);
            $table->string('modifier_type')->default('flat');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'expires_at']);
            $table->index(['user_id', 'stat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_active_effects');
        Schema::dropIfExists('item_effects');
        
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['effects', 'cooldown', 'duration', 'is_usable']);
        });
    }
};
