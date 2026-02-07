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
        if (!Schema::hasTable('player_bans')) Schema::create('player_bans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('banned_by')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['temporary', 'permanent'])->default('temporary');
            $table->text('reason');
            $table->dateTime('banned_at');
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('unbanned_at')->nullable();
            $table->foreignId('unbanned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('unban_reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_bans');
    }
};
