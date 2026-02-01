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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['news', 'event', 'maintenance', 'update', 'alert'])->default('news');
            $table->enum('target', ['all', 'online', 'level_range', 'location'])->default('all');
            $table->integer('min_level')->nullable();
            $table->integer('max_level')->nullable();
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->dateTime('published_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sticky')->default(false);
            $table->integer('views')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'published_at']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
