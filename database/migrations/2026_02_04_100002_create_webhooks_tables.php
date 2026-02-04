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
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('url');
            $table->text('secret')->nullable();
            $table->json('events')->default('["*"]');
            $table->boolean('is_active')->default(true);
            $table->json('headers')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(3);
            $table->timestamp('last_triggered_at')->nullable();
            $table->unsignedSmallInteger('last_response_code')->nullable();
            $table->unsignedSmallInteger('failure_count')->default(0);
            $table->timestamps();

            $table->index('is_active');
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->json('payload');
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->timestamp('delivered_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['webhook_id', 'created_at']);
            $table->index('event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
    }
};
