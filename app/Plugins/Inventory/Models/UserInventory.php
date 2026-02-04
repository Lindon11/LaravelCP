<?php

namespace App\Plugins\Inventory\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInventory extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
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
     * Add items to inventory.
     */
    public function addQuantity(int $amount): void
    {
        $this->increment('quantity', $amount);
    }

    /**
     * Remove items from inventory.
     */
    public function removeQuantity(int $amount): bool
    {
        if ($this->quantity < $amount) {
            return false;
        }

        $this->decrement('quantity', $amount);

        if ($this->quantity <= 0) {
            $this->delete();
        }

        return true;
    }

    /**
     * Check if has enough quantity.
     */
    public function hasQuantity(int $amount): bool
    {
        return $this->quantity >= $amount;
    }
}
