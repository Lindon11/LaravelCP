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
        Schema::table('combat_logs', function (Blueprint $table) {
            // Drop old foreign keys
            $table->dropForeign(['attacker_id']);
            $table->dropForeign(['defender_id']);
            
            // Recreate with users reference
            $table->foreign('attacker_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('defender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('combat_logs', function (Blueprint $table) {
            // Drop users foreign keys
            $table->dropForeign(['attacker_id']);
            $table->dropForeign(['defender_id']);
            
            // Recreate with players reference
            $table->foreign('attacker_id')->references('id')->on('players')->onDelete('cascade');
            $table->foreign('defender_id')->references('id')->on('players')->onDelete('cascade');
        });
    }
};
