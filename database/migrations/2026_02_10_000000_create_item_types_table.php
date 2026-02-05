<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // slug/key: weapon, armor, etc.
            $table->string('label');           // Display name: Weapons, Armor, etc.
            $table->string('icon')->default('CubeIcon'); // Heroicon component name
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_types');
    }
};
