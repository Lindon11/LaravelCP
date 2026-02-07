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
        if (!Schema::hasTable('license_keys')) Schema::create('license_keys', function (Blueprint $table) {
            $table->id();
            $table->string('license_id', 8)->unique(); // The hex ID embedded in the key payload
            $table->string('customer');
            $table->string('email');
            $table->string('domain')->default('*');
            $table->string('tier')->default('standard');
            $table->string('expires')->default('lifetime');
            $table->integer('max_users')->default(0);
            $table->string('plugins')->default('all');
            $table->text('masked_key'); // Masked version for display (LCP-UNL-eyJk...xz4qw)
            $table->boolean('is_activated')->default(false);
            $table->string('activated_domain')->nullable(); // Domain that activated the key
            $table->string('activated_ip')->nullable(); // IP that activated the key
            $table->timestamp('activated_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_revoked')->default(false);
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_keys');
    }
};
