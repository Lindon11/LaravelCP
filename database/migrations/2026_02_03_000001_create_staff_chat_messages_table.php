<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('staff_chat_messages')) Schema::create('staff_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->foreignId('mentioned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // Track which messages have been read by which users
        if (!Schema::hasTable('staff_chat_read_status')) Schema::create('staff_chat_read_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('last_read_message_id')->nullable()->constrained('staff_chat_messages')->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_chat_read_status');
        Schema::dropIfExists('staff_chat_messages');
    }
};
