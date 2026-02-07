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
        if (!Schema::hasTable('gangs')) Schema::create('gangs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->foreignId('leader_id')->constrained('users')->cascadeOnDelete();
            $table->bigInteger('bank')->default(0);
            $table->integer('respect')->default(0);
            $table->string('tag', 10)->nullable();
            $table->string('logo')->nullable();
            $table->integer('level')->default(1);
            $table->integer('max_members')->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gangs');
    }
};
