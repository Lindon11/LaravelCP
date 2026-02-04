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
        Schema::create('alliances', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('tag', 10)->unique();
            $table->text('description')->nullable();
            $table->foreignId('leader_id')->constrained('users')->onDelete('cascade');
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->integer('level')->default(1);
            $table->integer('experience')->default(0);
            $table->integer('reputation')->default(0);
            $table->bigInteger('bank_balance')->default(0);
            $table->integer('max_members')->default(10);
            $table->json('settings')->nullable();
            $table->json('ranks')->nullable(); // custom rank definitions
            $table->boolean('is_recruiting')->default(true);
            $table->integer('min_level_requirement')->default(1);
            $table->timestamps();

            $table->index('level');
            $table->index('reputation');
        });

        Schema::create('alliance_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alliance_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('rank')->default('member'); // leader, officer, member, recruit
            $table->integer('contribution')->default(0);
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->unique(['alliance_id', 'user_id']);
            $table->index(['user_id']);
        });

        Schema::create('territories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // city, region, landmark
            $table->foreignId('owner_alliance_id')->nullable()->constrained('alliances')->onDelete('set null');
            $table->integer('income_per_hour')->default(0);
            $table->integer('defense_bonus')->default(0);
            $table->json('bonuses')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('contestable_at')->nullable();
            $table->timestamps();

            $table->index('owner_alliance_id');
        });

        Schema::create('alliance_wars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aggressor_id')->constrained('alliances')->onDelete('cascade');
            $table->foreignId('defender_id')->constrained('alliances')->onDelete('cascade');
            $table->foreignId('territory_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('declared'); // declared, active, ended
            $table->integer('aggressor_score')->default(0);
            $table->integer('defender_score')->default(0);
            $table->foreignId('winner_id')->nullable()->constrained('alliances')->onDelete('set null');
            $table->text('reason')->nullable();
            $table->timestamp('declared_at');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->json('battle_log')->nullable();
            $table->timestamps();

            $table->index(['status', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alliance_wars');
        Schema::dropIfExists('territories');
        Schema::dropIfExists('alliance_members');
        Schema::dropIfExists('alliances');
    }
};
