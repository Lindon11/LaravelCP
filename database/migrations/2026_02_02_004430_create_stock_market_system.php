<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stocks/Companies that can be traded
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 10)->unique();
            $table->string('name');
            $table->string('sector');
            $table->text('description');
            $table->decimal('current_price', 15, 2)->default(100);
            $table->decimal('day_open', 15, 2)->default(100);
            $table->decimal('day_high', 15, 2)->default(100);
            $table->decimal('day_low', 15, 2)->default(100);
            $table->bigInteger('shares_available')->default(1000000);
            $table->bigInteger('shares_traded')->default(0);
            $table->decimal('market_cap', 20, 2)->default(0);
            $table->decimal('volatility', 8, 2)->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Stock price history
        Schema::create('stock_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 15, 2);
            $table->decimal('open_price', 15, 2);
            $table->decimal('close_price', 15, 2);
            $table->decimal('high_price', 15, 2);
            $table->decimal('low_price', 15, 2);
            $table->bigInteger('volume')->default(0);
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();
            
            $table->index(['stock_id', 'recorded_at']);
        });

        // User stock portfolios
        Schema::create('user_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('shares')->default(0);
            $table->decimal('average_buy_price', 15, 2)->default(0);
            $table->decimal('total_invested', 15, 2)->default(0);
            $table->decimal('current_value', 15, 2)->default(0);
            $table->decimal('profit_loss', 15, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'stock_id']);
            $table->index('user_id');
        });

        // Stock transactions
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['buy', 'sell']);
            $table->bigInteger('shares');
            $table->decimal('price_per_share', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('fees', 15, 2)->default(0);
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
        Schema::dropIfExists('user_stocks');
        Schema::dropIfExists('stock_price_history');
        Schema::dropIfExists('stocks');
    }
};
