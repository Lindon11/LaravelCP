<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('installed_modules', 'installed_plugins');

        // Update type column values from 'module' to 'plugin'
        DB::table('installed_plugins')->where('type', 'module')->update(['type' => 'plugin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert type column values from 'plugin' to 'module'
        DB::table('installed_plugins')->where('type', 'plugin')->update(['type' => 'module']);

        Schema::rename('installed_plugins', 'installed_modules');
    }
};
