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
        Schema::create('races', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('entry_fee')->default(1000);
            $table->integer('prize_pool')->default(0);
            $table->integer('min_participants')->default(2);
            $table->integer('max_participants')->default(8);
            $table->enum('status', ['waiting', 'racing', 'finished'])->default('waiting');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('winner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};
