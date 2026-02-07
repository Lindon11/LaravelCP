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
        if (!Schema::hasTable('email_templates')) Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // welcome, password-reset, etc.
            $table->string('name'); // Display name
            $table->string('subject');
            $table->text('body_html'); // HTML content with placeholders
            $table->text('body_text')->nullable(); // Plain text fallback
            $table->json('available_variables')->nullable(); // List of available placeholders
            $table->string('description')->nullable(); // Help text for admins
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
