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
        Schema::create('bounties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('placed_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('claimed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('status', ['active', 'claimed', 'expired'])->default('active');
            $table->text('reason')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bounties');
    }
};
