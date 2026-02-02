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
        Schema::create('installed_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version');
            $table->enum('type', ['module', 'theme', 'plugin'])->default('module');
            $table->text('description')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('config')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamp('installed_at')->useCurrent();
            $table->timestamps();

            $table->index(['type', 'enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installed_modules');
    }
};
