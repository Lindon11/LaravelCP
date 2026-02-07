<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('api_keys')) Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key', 64)->unique();
            $table->string('secret', 64)->nullable();
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->json('allowed_ips')->nullable();
            $table->json('allowed_domains')->nullable();
            $table->integer('rate_limit')->default(60); // requests per minute
            $table->integer('daily_limit')->nullable(); // requests per day
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->bigInteger('total_requests')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        if (!Schema::hasTable('api_request_logs')) Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_key_id')->constrained()->cascadeOnDelete();
            $table->string('method', 10);
            $table->string('endpoint');
            $table->integer('status_code');
            $table->integer('response_time')->nullable(); // milliseconds
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();
            $table->json('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['api_key_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
        Schema::dropIfExists('api_keys');
    }
};
