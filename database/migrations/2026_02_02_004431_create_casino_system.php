<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Casino games
        Schema::create('casino_games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // slots, roulette, blackjack, poker, dice
            $table->text('description');
            $table->integer('min_bet')->default(100);
            $table->integer('max_bet')->default(10000);
            $table->decimal('house_edge', 5, 2)->default(5); // Percentage
            $table->decimal('return_to_player', 5, 2)->default(95); // RTP percentage
            $table->json('rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Casino bets/plays history
        Schema::create('casino_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('casino_games')->cascadeOnDelete();
            $table->integer('bet_amount');
            $table->integer('payout')->default(0);
            $table->integer('profit_loss');
            $table->enum('result', ['win', 'loss', 'push'])->default('loss');
            $table->json('game_data')->nullable(); // Specific game data (cards, numbers, etc.)
            $table->timestamp('played_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'played_at']);
            $table->index(['game_id', 'played_at']);
        });

        // Lottery system
        Schema::create('lotteries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('ticket_price')->default(1000);
            $table->integer('prize_pool')->default(0);
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('winning_numbers')->nullable();
            $table->enum('status', ['active', 'drawn', 'cancelled'])->default('active');
            $table->timestamp('draw_date')->nullable();
            $table->timestamps();
        });

        // Lottery tickets
        Schema::create('lottery_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('numbers');
            $table->boolean('is_winner')->default(false);
            $table->integer('prize_amount')->default(0);
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();
            
            $table->index(['lottery_id', 'user_id']);
        });

        // Casino statistics per user
        Schema::create('user_casino_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('total_bets')->default(0);
            $table->integer('total_wagered')->default(0);
            $table->integer('total_won')->default(0);
            $table->integer('total_lost')->default(0);
            $table->integer('net_profit')->default(0);
            $table->integer('biggest_win')->default(0);
            $table->integer('biggest_loss')->default(0);
            $table->integer('current_streak')->default(0);
            $table->integer('best_streak')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_casino_stats');
        Schema::dropIfExists('lottery_tickets');
        Schema::dropIfExists('lotteries');
        Schema::dropIfExists('casino_bets');
        Schema::dropIfExists('casino_games');
    }
};
