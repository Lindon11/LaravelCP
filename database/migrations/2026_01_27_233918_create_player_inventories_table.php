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
        if (!Schema::hasTable('player_inventories')) Schema::create('player_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->boolean('equipped')->default(false);
            $table->string('slot')->nullable(); // weapon, armor, vehicle
            $table->timestamps();
            
            $table->unique(['user_id', 'item_id', 'slot']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_inventories');
    }
};
