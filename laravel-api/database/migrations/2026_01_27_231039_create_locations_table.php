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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->decimal('travel_cost', 10, 2)->default(0);
            $table->integer('required_level')->default(1);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        // Add location_id to players table
        Schema::table('players', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->default(1)->constrained('locations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
        
        Schema::dropIfExists('locations');
    }
};
