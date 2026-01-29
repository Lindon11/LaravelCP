<?php

namespace App\Events\Module;

use App\Models\User;

/**
 * Event fired when a player logs in
 */
class OnPlayerLogin extends ModuleHookEvent
{
    public function __construct(
        public User $player,
        public string $ipAddress
    ) {}

    public static function getName(): string
    {
        return 'OnPlayerLogin';
    }

    public function getData(): array
    {
        return [
            'player' => $this->player,
            'ip_address' => $this->ipAddress,
        ];
    }
}
