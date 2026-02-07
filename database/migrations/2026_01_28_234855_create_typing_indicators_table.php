<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('typing_indicators')) Schema::create('typing_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('chat_channels')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('last_typed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['channel_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typing_indicators');
    }
};
