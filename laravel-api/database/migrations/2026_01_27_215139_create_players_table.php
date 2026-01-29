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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('username')->unique();
            $table->integer('level')->default(1);
            $table->integer('health')->default(100);
            $table->integer('max_health')->default(100);
            $table->bigInteger('cash')->default(1000);
            $table->bigInteger('bank')->default(0);
            $table->integer('respect')->default(0);
            $table->integer('bullets')->default(10);
            $table->string('rank')->default('Thug');
            $table->timestamp('last_crime_at')->nullable();
            $table->timestamp('last_gta_at')->nullable();
            $table->timestamp('jail_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
