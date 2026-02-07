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
        if (!Schema::hasTable('combat_logs')) Schema::create('combat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attacker_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('defender_id')->constrained('users')->onDelete('cascade');
            $table->integer('damage_dealt')->default(0);
            $table->integer('attacker_health_before');
            $table->integer('attacker_health_after');
            $table->integer('defender_health_before');
            $table->integer('defender_health_after');
            $table->foreignId('weapon_used')->nullable()->constrained('items')->onDelete('set null');
            $table->enum('outcome', ['success', 'failed', 'killed'])->default('success');
            $table->integer('respect_gained')->default(0);
            $table->integer('cash_stolen')->default(0);
            $table->boolean('defender_in_hospital')->default(false);
            $table->timestamps();

            $table->index(['attacker_id', 'created_at']);
            $table->index(['defender_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combat_logs');
    }
};
