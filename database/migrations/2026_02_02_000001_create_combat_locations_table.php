<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combat_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('energy_cost')->default(20);
            $table->integer('min_level')->default(1);
            $table->boolean('active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('combat_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('combat_locations')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('difficulty')->default(1); // 1-5 skulls
            $table->integer('min_level')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('combat_enemies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('combat_areas')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('level');
            $table->integer('health');
            $table->integer('max_health');
            $table->integer('strength');
            $table->integer('defense');
            $table->integer('speed');
            $table->integer('agility');
            $table->string('weakness')->nullable(); // e.g., "Piercing", "Blunt", "Fire"
            $table->integer('difficulty')->default(1); // 1-5
            $table->integer('experience_reward')->default(0);
            $table->integer('cash_reward_min')->default(0);
            $table->integer('cash_reward_max')->default(0);
            $table->decimal('spawn_rate', 5, 2)->default(1.00); // 0.01 to 1.00
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('combat_fights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('enemy_id')->constrained('combat_enemies')->onDelete('cascade');
            $table->foreignId('area_id')->constrained('combat_areas')->onDelete('cascade');
            $table->integer('enemy_health');
            $table->integer('enemy_max_health');
            $table->integer('player_health_start');
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->string('status')->default('active'); // active, won, lost, fled
            $table->timestamps();
        });

        Schema::create('combat_fight_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fight_id')->constrained('combat_fights')->onDelete('cascade');
            $table->string('attacker_type'); // 'player' or 'enemy'
            $table->integer('damage');
            $table->boolean('critical')->default(false);
            $table->boolean('missed')->default(false);
            $table->string('weapon_used')->nullable();
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combat_fight_logs');
        Schema::dropIfExists('combat_fights');
        Schema::dropIfExists('combat_enemies');
        Schema::dropIfExists('combat_areas');
        Schema::dropIfExists('combat_locations');
    }
};
