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
        if (!Schema::hasTable('user_equipment')) Schema::create('user_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('slot', ['weapon', 'armor', 'helmet', 'gloves', 'boots', 'accessory', 'vehicle']);
            $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            
            // Unique constraint: one item per slot per user
            $table->unique(['user_id', 'slot']);
            
            // Indexes
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_equipment');
    }
};
