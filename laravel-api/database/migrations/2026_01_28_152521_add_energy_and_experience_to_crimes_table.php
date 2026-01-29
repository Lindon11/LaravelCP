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
        Schema::table('crimes', function (Blueprint $table) {
            $table->integer('energy_cost')->default(5)->after('cooldown_seconds');
            $table->integer('experience_reward')->default(10)->after('respect_reward');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crimes', function (Blueprint $table) {
            $table->dropColumn(['energy_cost', 'experience_reward']);
        });
    }
};
