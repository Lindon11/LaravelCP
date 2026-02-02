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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'strength')) {
                $table->integer('strength')->default(10);
            }
            if (!Schema::hasColumn('users', 'defense')) {
                $table->integer('defense')->default(10);
            }
            if (!Schema::hasColumn('users', 'speed')) {
                $table->integer('speed')->default(10);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['strength', 'defense', 'speed']);
        });
    }
};
