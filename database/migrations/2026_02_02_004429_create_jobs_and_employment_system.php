<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Companies table
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type');
                $table->text('description');
                $table->integer('rating')->default(0);
                $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
                $table->integer('employees_count')->default(0);
                $table->integer('total_profit')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Employment positions table (avoiding conflict with Laravel's queue jobs table)
        if (!Schema::hasTable('employment_positions')) {
            Schema::create('employment_positions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('description');
                $table->integer('required_level')->default(1);
                $table->integer('required_intelligence')->default(0);
                $table->integer('required_endurance')->default(0);
                $table->integer('base_salary')->default(0);
                $table->integer('max_employees')->default(10);
                $table->integer('current_employees')->default(0);
                $table->json('perks')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // User employment table
        if (!Schema::hasTable('player_employment')) {
            Schema::create('player_employment', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('position_id')->constrained('employment_positions')->cascadeOnDelete();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->integer('salary')->default(0);
                $table->integer('performance_rating')->default(50);
                $table->timestamp('hired_at')->nullable();
                $table->timestamp('last_work_at')->nullable();
                $table->integer('total_days_worked')->default(0);
                $table->integer('total_earned')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique('user_id');
                $table->index(['company_id', 'is_active']);
            });
        }

        // Work shifts table
        if (!Schema::hasTable('work_shifts')) {
            Schema::create('work_shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('player_employment_id')->constrained('player_employment')->cascadeOnDelete();
                $table->integer('earnings');
                $table->integer('performance_score');
                $table->timestamp('worked_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'worked_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('work_shifts');
        Schema::dropIfExists('player_employment');
        Schema::dropIfExists('employment_positions');
        Schema::dropIfExists('companies');
    }
};
