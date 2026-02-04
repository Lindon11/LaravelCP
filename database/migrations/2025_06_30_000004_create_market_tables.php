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
        Schema::create('market_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('item_type'); // polymorphic type
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity')->default(1);
            $table->integer('price');
            $table->string('listing_type')->default('fixed'); // fixed, auction
            $table->integer('starting_bid')->nullable();
            $table->integer('current_bid')->nullable();
            $table->foreignId('highest_bidder_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('buy_now_price')->nullable();
            $table->string('status')->default('active'); // active, sold, expired, cancelled
            $table->timestamp('expires_at');
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index(['item_type', 'item_id']);
            $table->index('seller_id');
        });

        Schema::create('market_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('market_listings')->onDelete('cascade');
            $table->foreignId('bidder_id')->constrained('users')->onDelete('cascade');
            $table->integer('amount');
            $table->boolean('is_winning')->default(false);
            $table->timestamps();

            $table->index(['listing_id', 'amount']);
        });

        Schema::create('trade_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->json('offered_items')->nullable();
            $table->integer('offered_money')->default(0);
            $table->json('requested_items')->nullable();
            $table->integer('requested_money')->default(0);
            $table->string('status')->default('pending'); // pending, accepted, rejected, cancelled, expired
            $table->text('message')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_id', 'status']);
            $table->index(['initiator_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_offers');
        Schema::dropIfExists('market_bids');
        Schema::dropIfExists('market_listings');
    }
};
