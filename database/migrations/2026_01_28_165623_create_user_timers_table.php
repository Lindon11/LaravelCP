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
        if (!Schema::hasTable('user_timers')) Schema::create('user_timers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('timer_name'); // crime, jail, hospital, action, etc
            $table->timestamp('expires_at')->nullable();
            $table->integer('duration')->default(0); // Duration in seconds for reference
            $table->text('metadata')->nullable(); // JSON data for timer context
            $table->timestamps();

            $table->index(['user_id', 'timer_name']);
            $table->index('expires_at');
            $table->unique(['user_id', 'timer_name']); // One timer per type per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_timers');
    }
};
