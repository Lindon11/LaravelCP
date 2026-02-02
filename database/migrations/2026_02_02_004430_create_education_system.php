<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Education courses table
        Schema::create('education_courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // intelligence, endurance, mixed
            $table->text('description');
            $table->integer('required_level')->default(1);
            $table->integer('required_intelligence')->default(0);
            $table->integer('required_endurance')->default(0);
            $table->integer('duration_hours')->default(24);
            $table->integer('cost')->default(0);
            $table->integer('intelligence_reward')->default(0);
            $table->integer('endurance_reward')->default(0);
            $table->json('perks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // User education enrollments
        Schema::create('user_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('education_courses')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->integer('progress_percentage')->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });

        // Add intelligence and endurance to users if not exists
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'intelligence')) {
                $table->integer('intelligence')->default(10)->after('speed');
            }
            if (!Schema::hasColumn('users', 'endurance')) {
                $table->integer('endurance')->default(10)->after('intelligence');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_education');
        Schema::dropIfExists('education_courses');
        
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'intelligence')) {
                $table->dropColumn('intelligence');
            }
            if (Schema::hasColumn('users', 'endurance')) {
                $table->dropColumn('endurance');
            }
        });
    }
};
