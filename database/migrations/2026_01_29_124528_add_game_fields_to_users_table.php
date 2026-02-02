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
            $table->string('username')->nullable()->after('name');
            $table->integer('level')->default(1)->after('username');
            $table->integer('experience')->default(0)->after('level');
            $table->integer('health')->default(100)->after('experience');
            $table->integer('max_health')->default(100)->after('health');
            $table->integer('energy')->default(100)->after('max_health');
            $table->integer('max_energy')->default(100)->after('energy');
            $table->bigInteger('cash')->default(1000)->after('max_energy');
            $table->bigInteger('bank')->default(0)->after('cash');
            $table->integer('respect')->default(0)->after('bank');
            $table->integer('bullets')->default(10)->after('respect');
            $table->string('rank')->default('Thug')->after('bullets');
            $table->string('location')->default('Chicago')->after('rank');
            $table->timestamp('last_crime_at')->nullable()->after('location');
            $table->timestamp('last_gta_at')->nullable()->after('last_crime_at');
            $table->timestamp('last_active')->nullable()->after('last_gta_at');
            $table->timestamp('jail_until')->nullable()->after('last_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'level',
                'experience',
                'health',
                'max_health',
                'energy',
                'max_energy',
                'cash',
                'bank',
                'respect',
                'bullets',
                'rank',
                'location',
                'last_crime_at',
                'last_gta_at',
                'last_active',
                'jail_until',
            ]);
        });
    }
};
