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
        if (!Schema::hasTable('crimes')) Schema::create('crimes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('success_rate')->default(50);
            $table->integer('min_cash')->default(100);
            $table->integer('max_cash')->default(500);
            $table->integer('respect_reward')->default(1);
            $table->integer('cooldown_seconds')->default(30);
            $table->integer('required_level')->default(1);
            $table->string('difficulty')->default('easy');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crimes');
    }
};
