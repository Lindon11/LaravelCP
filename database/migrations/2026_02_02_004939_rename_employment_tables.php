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
        // Create employment_positions table (formerly jobs)
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

        // Rename user_employment to player_employment if it hasn't been renamed yet
        if (Schema::hasTable('user_employment') && !Schema::hasTable('player_employment')) {
            Schema::rename('user_employment', 'player_employment');
        }

        // Update foreign key reference if needed
        if (Schema::hasTable('player_employment') && Schema::hasColumn('player_employment', 'job_id')) {
            Schema::table('player_employment', function (Blueprint $table) {
                $table->dropForeign(['job_id']);
                $table->renameColumn('job_id', 'position_id');
            });

            Schema::table('player_employment', function (Blueprint $table) {
                $table->foreign('position_id')->references('id')->on('employment_positions')->cascadeOnDelete();
            });
        }

        // Update work_shifts if needed
        if (Schema::hasTable('work_shifts') && Schema::hasColumn('work_shifts', 'employment_id')) {
            Schema::table('work_shifts', function (Blueprint $table) {
                $table->dropForeign(['employment_id']);
                $table->renameColumn('employment_id', 'player_employment_id');
            });

            Schema::table('work_shifts', function (Blueprint $table) {
                $table->foreign('player_employment_id')->references('id')->on('player_employment')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_positions');
        Schema::rename('player_employment', 'user_employment');
    }
};
