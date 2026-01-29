<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cars available in the game
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('value'); // Base value of the car
            $table->integer('theft_chance')->default(100); // Weight for random selection
            $table->integer('required_level')->default(1);
            $table->timestamps();
        });

        // Theft difficulty types
        Schema::create('theft_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Easy Target", "Risky Job", "High Stakes"
            $table->text('description')->nullable();
            $table->integer('success_rate'); // Base success chance (0-100)
            $table->integer('jail_multiplier')->default(35); // jail_time = id * multiplier
            $table->integer('min_car_value'); // Minimum car value for this theft type
            $table->integer('max_car_value'); // Maximum car value for this theft type
            $table->integer('max_damage'); // Maximum % damage the car can have
            $table->integer('cooldown')->default(180); // Seconds between attempts
            $table->integer('required_level')->default(1);
            $table->timestamps();
        });

        // Player's garage (stolen cars)
        Schema::create('garages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('car_id')->constrained('cars')->onDelete('cascade');
            $table->integer('damage')->default(0); // 0-100% damage
            $table->string('location')->default('Chicago'); // City where car is stored
            $table->timestamps();
            
            $table->index('user_id');
        });

        // Theft attempt history
        Schema::create('theft_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('theft_type_id')->constrained('theft_types')->onDelete('cascade');
            $table->foreignId('car_id')->nullable()->constrained('cars')->onDelete('set null');
            $table->boolean('success')->default(false);
            $table->boolean('caught')->default(false); // Caught and jailed
            $table->integer('car_value')->default(0); // Value of car stolen (after damage)
            $table->integer('damage')->default(0); // % damage on stolen car
            $table->text('result_message')->nullable();
            $table->timestamp('attempted_at')->useCurrent();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('attempted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theft_attempts');
        Schema::dropIfExists('garages');
        Schema::dropIfExists('theft_types');
        Schema::dropIfExists('cars');
    }
};
