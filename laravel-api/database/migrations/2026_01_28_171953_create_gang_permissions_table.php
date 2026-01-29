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
        Schema::create('gang_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gang_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['boss', 'underboss', 'captain', 'member'])->default('member');
            $table->boolean('can_invite')->default(false);
            $table->boolean('can_kick')->default(false);
            $table->boolean('can_promote')->default(false);
            $table->boolean('can_demote')->default(false);
            $table->boolean('can_manage_bank')->default(false);
            $table->boolean('can_manage_property')->default(false);
            $table->boolean('can_manage_wars')->default(false);
            $table->boolean('can_send_mass_message')->default(false);
            $table->timestamps();
            
            // Unique constraint: one permission record per player per gang
            $table->unique(['gang_id', 'user_id']);
            
            // Indexes for performance
            $table->index(['gang_id', 'role']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gang_permissions');
    }
};
