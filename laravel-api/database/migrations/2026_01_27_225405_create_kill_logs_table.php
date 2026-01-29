<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kill_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('killer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('victim_id')->constrained('users')->onDelete('cascade');
            $table->integer('bullets_used');
            $table->integer('damage_dealt');
            $table->boolean('successful')->default(false);
            $table->timestamp('killed_at')->useCurrent();
            
            $table->index('killer_id');
            $table->index('victim_id');
            $table->index('killed_at');
        });

        // Add killed_by and died_at to players table
        Schema::table('players', function (Blueprint $table) {
            $table->foreignId('killed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('died_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropForeign(['killed_by']);
            $table->dropColumn(['killed_by', 'died_at']);
        });
        
        Schema::dropIfExists('kill_logs');
    }
};
