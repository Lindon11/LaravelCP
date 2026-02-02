<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Item market listings
        Schema::create('item_market_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->integer('price_per_unit');
            $table->integer('total_price');
            $table->enum('status', ['active', 'sold', 'cancelled', 'expired'])->default('active');
            $table->text('description')->nullable();
            $table->timestamp('listed_at')->nullable()->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->index(['item_id', 'status', 'price_per_unit']);
            $table->index(['seller_id', 'status']);
        });

        // Item market transactions
        Schema::create('item_market_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('item_market_listings')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('quantity');
            $table->integer('price_per_unit');
            $table->integer('total_price');
            $table->integer('market_fee')->default(0); // 1-5% fee
            $table->integer('seller_receives');
            $table->timestamp('completed_at')->nullable()->useCurrent();
            $table->timestamps();

            $table->index(['buyer_id', 'completed_at']);
            $table->index(['seller_id', 'completed_at']);
            $table->index(['item_id', 'completed_at']);
        });

        // Item price history for market analysis
        Schema::create('item_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('average_price');
            $table->integer('lowest_price');
            $table->integer('highest_price');
            $table->integer('total_sold');
            $table->date('date');
            $table->timestamps();

            $table->unique(['item_id', 'date']);
            $table->index('date');
        });

        // Points market (for trading game points/credits)
        Schema::create('points_market_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->integer('points_amount');
            $table->integer('cash_price');
            $table->decimal('rate', 10, 2); // Points per $1
            $table->enum('status', ['active', 'sold', 'cancelled'])->default('active');
            $table->timestamp('listed_at')->nullable()->useCurrent();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'rate']);
        });

        // Points transactions
        Schema::create('points_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('points_market_listings')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->integer('points_amount');
            $table->integer('cash_paid');
            $table->timestamp('completed_at')->nullable()->useCurrent();
            $table->timestamps();
        });

        // Add points column to users if not exists
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'points')) {
                $table->integer('points')->default(0)->after('bullets');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'points')) {
                $table->dropColumn('points');
            }
        });

        Schema::dropIfExists('points_transactions');
        Schema::dropIfExists('points_market_listings');
        Schema::dropIfExists('item_price_history');
        Schema::dropIfExists('item_market_transactions');
        Schema::dropIfExists('item_market_listings');
    }
};
