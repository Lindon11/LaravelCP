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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // weapon, armor, consumable, vehicle, misc
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('price')->default(0);
            $table->integer('sell_price')->default(0);
            $table->boolean('tradeable')->default(true);
            $table->boolean('stackable')->default(false);
            $table->integer('max_stack')->default(1);
            $table->json('stats')->nullable(); // damage, defense, speed, etc.
            $table->json('requirements')->nullable(); // level, etc.
            $table->string('rarity')->default('common'); // common, uncommon, rare, epic, legendary
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
