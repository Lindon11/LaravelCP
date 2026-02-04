<?php

namespace App\Plugins\Drugs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drug extends Model
{
    protected $fillable = [
        'name',
        'description',
        'base_price',
        'min_price',
        'max_price',
        'bust_chance',
        'image',
    ];

    protected $casts = [
        'bust_chance' => 'decimal:2',
    ];

    public function playerDrugs(): HasMany
    {
        return $this->hasMany(PlayerDrug::class);
    }

    public function getPriceForLocation($locationId): int
    {
        // Price varies by location (seeded random per location)
        $seed = $this->id * 1000 + $locationId;
        mt_srand($seed);
        $price = mt_rand($this->min_price, $this->max_price);
        mt_srand(); // Reset seed
        return $price;
    }
}
