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
        if (!Schema::hasTable('error_logs')) Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // Exception type
            $table->text('message'); // Error message
            $table->string('file')->nullable(); // File where error occurred
            $table->integer('line')->nullable(); // Line number
            $table->text('trace')->nullable(); // Stack trace
            $table->string('url')->nullable(); // URL where error occurred
            $table->string('method')->nullable(); // HTTP method
            $table->string('ip')->nullable(); // User IP
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // User if logged in
            $table->text('user_agent')->nullable(); // Browser info
            $table->json('context')->nullable(); // Additional context
            $table->boolean('resolved')->default(false)->index(); // Mark as resolved
            $table->integer('count')->default(1); // Number of occurrences
            $table->timestamp('last_seen_at')->useCurrent(); // Last occurrence
            $table->timestamps();
            
            // Index for performance
            $table->index(['created_at', 'resolved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
