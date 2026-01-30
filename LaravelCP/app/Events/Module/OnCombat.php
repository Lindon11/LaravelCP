<?php

namespace App\Events\Module;

use App\Models\User;

/**
 * Event fired when combat occurs between players
 */
class OnCombat extends ModuleHookEvent
{
    public function __construct(
        public User $attacker,
        public User $defender,
        public bool $attackerWon,
        public int $damageDealt,
        public ?int $cashStolen = null
    ) {}

    public static function getName(): string
    {
        return 'OnCombat';
    }

    public function getData(): array
    {
        return [
            'attacker' => $this->attacker,
            'defender' => $this->defender,
            'attacker_won' => $this->attackerWon,
            'damage_dealt' => $this->damageDealt,
            'cash_stolen' => $this->cashStolen,
        ];
    }
}
