<?php

namespace App\Core\Events\Module;

use App\Core\Models\User;

/**
 * Event fired when a player levels up
 */
class OnLevelUp extends ModuleHookEvent
{
    public function __construct(
        public User $player,
        public int $oldLevel,
        public int $newLevel
    ) {}

    public static function getName(): string
    {
        return 'OnLevelUp';
    }

    public function getData(): array
    {
        return [
            'player' => $this->player,
            'old_level' => $this->oldLevel,
            'new_level' => $this->newLevel,
        ];
    }
}
