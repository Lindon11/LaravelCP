<?php

namespace App\Events\Module;

use App\Models\User;

/**
 * Event fired when a player travels to a new location
 */
class OnTravel extends ModuleHookEvent
{
    public function __construct(
        public User $player,
        public string $fromLocation,
        public string $toLocation
    ) {}

    public static function getName(): string
    {
        return 'OnTravel';
    }

    public function getData(): array
    {
        return [
            'player' => $this->player,
            'from_location' => $this->fromLocation,
            'to_location' => $this->toLocation,
        ];
    }
}
