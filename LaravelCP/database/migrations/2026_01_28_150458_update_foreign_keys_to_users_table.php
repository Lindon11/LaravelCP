<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop all foreign keys referencing players table
        Schema::table('crime_attempts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('player_inventories', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('player_drugs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('player_missions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('forum_topics', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('race_participants', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('player_bans', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('player_warnings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('theft_attempts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('garages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('detective_reports', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        
        // Add foreign keys referencing users table
        Schema::table('crime_attempts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        Schema::table('player_inventories', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        Schema::table('player_drugs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        Schema::table('player_missions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        Schema::table('forum_topics', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        Schema::table('race_participants', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        Schema::table('player_bans', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        Schema::table('player_warnings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        Schema::table('theft_attempts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        Schema::table('garages', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        Schema::table('detective_reports', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
        
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Reverse the changes
    }
};
