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
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('required_exp')->default(0); // Experience needed to reach this rank
            $table->integer('max_health')->default(100); // Max health for this rank
            $table->integer('cash_reward')->default(0); // Cash given when reaching this rank
            $table->integer('bullet_reward')->default(0); // Bullets given when reaching this rank
            $table->integer('user_limit')->default(0); // Optional limit on users at this rank (0 = no limit)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
