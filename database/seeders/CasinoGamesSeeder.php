<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CasinoGamesSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            // Slots
            [
                'name' => 'Lucky 7s',
                'type' => 'slots',
                'description' => 'Classic slot machine with lucky 7 symbols',
                'min_bet' => 10,
                'max_bet' => 1000,
                'house_edge' => 5.0,
                'return_to_player' => 95.0,
                'rules' => json_encode([
                    'symbols' => ['7', '🍒', '🍋', '🔔', '⭐'],
                    'payouts' => [
                        '777' => 100,
                        '🍒🍒🍒' => 50,
                        '🍋🍋🍋' => 25,
                        '🔔🔔🔔' => 15,
                        '⭐⭐⭐' => 10,
                    ],
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Diamond Reels',
                'type' => 'slots',
                'description' => 'High stakes diamond-themed slots',
                'min_bet' => 50,
                'max_bet' => 5000,
                'house_edge' => 4.5,
                'return_to_player' => 95.5,
                'rules' => json_encode([
                    'symbols' => ['💎', '👑', '💰', '🎰', '🍀'],
                    'payouts' => [
                        '💎💎💎' => 200,
                        '👑👑👑' => 100,
                        '💰💰💰' => 50,
                    ],
                ]),
                'is_active' => true,
            ],
            
            // Roulette
            [
                'name' => 'European Roulette',
                'type' => 'roulette',
                'description' => 'Classic roulette with single zero',
                'min_bet' => 5,
                'max_bet' => 10000,
                'house_edge' => 2.7,
                'return_to_player' => 97.3,
                'rules' => json_encode([
                    'wheel' => range(0, 36),
                    'bets' => [
                        'straight' => ['payout' => 35],
                        'split' => ['payout' => 17],
                        'street' => ['payout' => 11],
                        'corner' => ['payout' => 8],
                        'red/black' => ['payout' => 1],
                        'even/odd' => ['payout' => 1],
                        'high/low' => ['payout' => 1],
                    ],
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'American Roulette',
                'type' => 'roulette',
                'description' => 'Roulette with double zero',
                'min_bet' => 5,
                'max_bet' => 10000,
                'house_edge' => 5.26,
                'return_to_player' => 94.74,
                'rules' => json_encode([
                    'wheel' => array_merge([0, '00'], range(1, 36)),
                ]),
                'is_active' => true,
            ],
            
            // Blackjack
            [
                'name' => 'Classic Blackjack',
                'type' => 'blackjack',
                'description' => 'Traditional blackjack - beat the dealer to 21',
                'min_bet' => 10,
                'max_bet' => 5000,
                'house_edge' => 0.5,
                'return_to_player' => 99.5,
                'rules' => json_encode([
                    'decks' => 6,
                    'dealer_hits_soft_17' => true,
                    'blackjack_payout' => 1.5,
                    'insurance_payout' => 2,
                    'double_down' => true,
                    'split' => true,
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Single Deck Blackjack',
                'type' => 'blackjack',
                'description' => 'Higher stakes single deck game',
                'min_bet' => 50,
                'max_bet' => 10000,
                'house_edge' => 0.17,
                'return_to_player' => 99.83,
                'rules' => json_encode([
                    'decks' => 1,
                    'dealer_hits_soft_17' => false,
                    'blackjack_payout' => 1.5,
                ]),
                'is_active' => true,
            ],
            
            // Poker
            [
                'name' => 'Texas Hold\'em',
                'type' => 'poker',
                'description' => 'Popular poker variant against the house',
                'min_bet' => 20,
                'max_bet' => 2000,
                'house_edge' => 2.0,
                'return_to_player' => 98.0,
                'rules' => json_encode([
                    'hands' => ['royal_flush', 'straight_flush', 'four_kind', 'full_house', 'flush', 'straight', 'three_kind', 'two_pair', 'pair'],
                    'payouts' => [
                        'royal_flush' => 500,
                        'straight_flush' => 100,
                        'four_kind' => 40,
                        'full_house' => 10,
                        'flush' => 6,
                        'straight' => 5,
                    ],
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Three Card Poker',
                'type' => 'poker',
                'description' => 'Fast-paced poker with three cards',
                'min_bet' => 10,
                'max_bet' => 1000,
                'house_edge' => 3.37,
                'return_to_player' => 96.63,
                'rules' => json_encode([
                    'ante_bonus' => true,
                    'pair_plus' => true,
                ]),
                'is_active' => true,
            ],
            
            // Dice
            [
                'name' => 'Craps',
                'type' => 'dice',
                'description' => 'Classic dice game with multiple betting options',
                'min_bet' => 5,
                'max_bet' => 5000,
                'house_edge' => 1.41,
                'return_to_player' => 98.59,
                'rules' => json_encode([
                    'dice' => 2,
                    'pass_line' => true,
                    'dont_pass' => true,
                    'come' => true,
                    'field' => true,
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'High/Low Dice',
                'type' => 'dice',
                'description' => 'Guess if the dice roll will be high or low',
                'min_bet' => 10,
                'max_bet' => 2000,
                'house_edge' => 2.78,
                'return_to_player' => 97.22,
                'rules' => json_encode([
                    'dice' => 2,
                    'low' => [2, 3, 4, 5, 6],
                    'high' => [8, 9, 10, 11, 12],
                    'tie' => 7,
                ]),
                'is_active' => true,
            ],
        ];

        foreach ($games as $game) {
            DB::table('casino_games')->insert(array_merge($game, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create a lottery
        DB::table('lotteries')->insert([
            'name' => 'Daily Jackpot',
            'description' => 'Daily lottery with massive prizes',
            'ticket_price' => 100,
            'draw_date' => now()->addDay(),
            'status' => 'active',
            'prize_pool' => 0,
            'winner_user_id' => null,
            'winning_numbers' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
