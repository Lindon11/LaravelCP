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
        if (!Schema::hasTable('gang_logs')) Schema::create('gang_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gang_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Actor
            $table->foreignId('target_player_id')->nullable()->constrained('users')->nullOnDelete(); // Target of action
            $table->string('action'); // joined, left, kicked, promoted, demoted, deposit, withdraw, etc.
            $table->text('details')->nullable(); // JSON or text details
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            // Indexes for performance
            $table->index(['gang_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gang_logs');
    }
};
