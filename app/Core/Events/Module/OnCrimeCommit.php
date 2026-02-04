<?php

namespace App\Core\Events\Module;

use App\Core\Models\User;

/**
 * Event fired when a crime is committed
 * 
 * Example usage:
 * ModuleService->registerHooks('missions', [
 *     'OnCrimeCommit' => function($data) {
 *         // Update mission progress for crime-related missions
 *     }
 * ]);
 */
class OnCrimeCommit extends ModuleHookEvent
{
    public function __construct(
        public User $player,
        public string $crimeType,
        public bool $success,
        public int $cashEarned,
        public int $respectEarned
    ) {}

    public static function getName(): string
    {
        return 'OnCrimeCommit';
    }

    public function getData(): array
    {
        return [
            'player' => $this->player,
            'crime_type' => $this->crimeType,
            'success' => $this->success,
            'cash_earned' => $this->cashEarned,
            'respect_earned' => $this->respectEarned,
        ];
    }
}
