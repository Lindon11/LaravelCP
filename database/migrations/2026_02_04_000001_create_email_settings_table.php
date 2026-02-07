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
        if (!Schema::hasTable('email_settings')) Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mailer')->default('smtp'); // smtp, mailgun, ses, postmark
            $table->string('host')->nullable();
            $table->integer('port')->default(587);
            $table->string('username')->nullable();
            $table->string('password')->nullable(); // encrypted
            $table->string('encryption')->default('tls'); // tls, ssl, null
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();

            // Mailgun specific
            $table->string('mailgun_domain')->nullable();
            $table->string('mailgun_secret')->nullable(); // encrypted
            $table->string('mailgun_endpoint')->default('api.mailgun.net');

            // General settings
            $table->boolean('is_active')->default(false);
            $table->timestamp('last_tested_at')->nullable();
            $table->boolean('test_successful')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};
