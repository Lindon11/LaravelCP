<?php

namespace App\Plugins\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEquipment extends Model
{
    protected $table = 'user_equipment';

    protected $fillable = [
        'user_id',
        'slot',
        'item_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Check if slot is equipped.
     */
    public function isEquipped(): bool
    {
        return $this->item_id !== null;
    }

    /**
     * Get stat bonuses from equipped item.
     */
    public function getStatBonuses(): array
    {
        if (!$this->isEquipped() || !$this->item) {
            return [];
        }

        $stats = $this->item->stats;
        
        // If stats is a JSON string, decode it
        if (is_string($stats)) {
            $stats = json_decode($stats, true);
        }

        return $stats ?? [];
    }
}
