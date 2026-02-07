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
        if (!Schema::hasTable('race_participants')) Schema::create('race_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('player_inventories')->nullOnDelete();
            $table->integer('bet_amount')->default(0);
            $table->integer('position')->nullable();
            $table->integer('finish_time')->nullable();
            $table->integer('winnings')->default(0);
            $table->timestamps();
            
            $table->unique(['race_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_participants');
    }
};
