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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // house, business, warehouse
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('income_per_day', 10, 2)->default(0);
            $table->integer('required_level')->default(1);
            $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
