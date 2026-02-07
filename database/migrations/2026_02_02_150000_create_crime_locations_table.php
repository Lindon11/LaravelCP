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
        if (!Schema::hasTable('crime_locations')) Schema::create('crime_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('energy_cost')->default(1);
            $table->integer('min_level')->default(1);
            $table->integer('success_rate_base')->default(50)->comment('Base success rate percentage');
            $table->integer('min_cash')->default(50);
            $table->integer('max_cash')->default(500);
            $table->integer('min_exp')->default(10);
            $table->integer('max_exp')->default(50);
            $table->integer('respect_reward')->default(1);
            $table->integer('jail_chance')->default(10)->comment('Chance to be jailed on failure (percentage)');
            $table->integer('cooldown_seconds')->default(30);
            $table->string('difficulty')->default('easy'); // easy, medium, hard
            $table->string('color_theme')->nullable()->comment('CSS gradient for UI card');
            $table->boolean('active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Seed initial crime locations
        DB::table('crime_locations')->insert([
            [
                'name' => 'Street Corner',
                'description' => 'A quiet street corner, perfect for petty crimes.',
                'energy_cost' => 1,
                'min_level' => 1,
                'success_rate_base' => 75,
                'min_cash' => 50,
                'max_cash' => 150,
                'min_exp' => 10,
                'max_exp' => 25,
                'respect_reward' => 1,
                'jail_chance' => 5,
                'cooldown_seconds' => 15,
                'difficulty' => 'easy',
                'color_theme' => 'linear-gradient(135deg, #64748b 0%, #475569 100%)',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dark Alley',
                'description' => 'A dimly lit alley where desperate souls gather.',
                'energy_cost' => 1,
                'min_level' => 1,
                'success_rate_base' => 70,
                'min_cash' => 75,
                'max_cash' => 200,
                'min_exp' => 15,
                'max_exp' => 30,
                'respect_reward' => 2,
                'jail_chance' => 8,
                'cooldown_seconds' => 20,
                'difficulty' => 'easy',
                'color_theme' => 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)',
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Convenience Store',
                'description' => 'A small store with light security.',
                'energy_cost' => 2,
                'min_level' => 3,
                'success_rate_base' => 60,
                'min_cash' => 150,
                'max_cash' => 400,
                'min_exp' => 25,
                'max_exp' => 50,
                'respect_reward' => 3,
                'jail_chance' => 12,
                'cooldown_seconds' => 30,
                'difficulty' => 'medium',
                'color_theme' => 'linear-gradient(135deg, #0891b2 0%, #0e7490 100%)',
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Residential Area',
                'description' => 'Wealthy homes with valuable items.',
                'energy_cost' => 2,
                'min_level' => 5,
                'success_rate_base' => 55,
                'min_cash' => 200,
                'max_cash' => 600,
                'min_exp' => 35,
                'max_exp' => 70,
                'respect_reward' => 4,
                'jail_chance' => 15,
                'cooldown_seconds' => 40,
                'difficulty' => 'medium',
                'color_theme' => 'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
                'order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Warehouse District',
                'description' => 'Industrial area with valuable cargo.',
                'energy_cost' => 3,
                'min_level' => 10,
                'success_rate_base' => 45,
                'min_cash' => 400,
                'max_cash' => 1000,
                'min_exp' => 60,
                'max_exp' => 100,
                'respect_reward' => 6,
                'jail_chance' => 20,
                'cooldown_seconds' => 60,
                'difficulty' => 'hard',
                'color_theme' => 'linear-gradient(135deg, #ea580c 0%, #c2410c 100%)',
                'order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Downtown Bank',
                'description' => 'A heavily guarded bank in the city center.',
                'energy_cost' => 3,
                'min_level' => 15,
                'success_rate_base' => 35,
                'min_cash' => 1000,
                'max_cash' => 2500,
                'min_exp' => 100,
                'max_exp' => 150,
                'respect_reward' => 10,
                'jail_chance' => 30,
                'cooldown_seconds' => 90,
                'difficulty' => 'hard',
                'color_theme' => 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)',
                'order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Casino',
                'description' => 'High-stakes crimes in the gambling district.',
                'energy_cost' => 5,
                'min_level' => 25,
                'success_rate_base' => 25,
                'min_cash' => 2000,
                'max_cash' => 5000,
                'min_exp' => 200,
                'max_exp' => 300,
                'respect_reward' => 15,
                'jail_chance' => 35,
                'cooldown_seconds' => 120,
                'difficulty' => 'extreme',
                'color_theme' => 'linear-gradient(135deg, #ca8a04 0%, #a16207 100%)',
                'order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mansion District',
                'description' => 'Luxurious estates with maximum security.',
                'energy_cost' => 5,
                'min_level' => 45,
                'success_rate_base' => 20,
                'min_cash' => 5000,
                'max_cash' => 10000,
                'min_exp' => 400,
                'max_exp' => 600,
                'respect_reward' => 25,
                'jail_chance' => 40,
                'cooldown_seconds' => 180,
                'difficulty' => 'extreme',
                'color_theme' => 'linear-gradient(135deg, #7c2d12 0%, #431407 100%)',
                'order' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crime_locations');
    }
};
