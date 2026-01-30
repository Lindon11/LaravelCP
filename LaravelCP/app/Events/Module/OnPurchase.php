<?php

namespace App\Events\Module;

use App\Models\User;

/**
 * Event fired when a player makes a purchase
 */
class OnPurchase extends ModuleHookEvent
{
    public function __construct(
        public User $player,
        public string $itemType,
        public mixed $item,
        public int $cost
    ) {}

    public static function getName(): string
    {
        return 'OnPurchase';
    }

    public function getData(): array
    {
        return [
            'player' => $this->player,
            'item_type' => $this->itemType,
            'item' => $this->item,
            'cost' => $this->cost,
        ];
    }
}
