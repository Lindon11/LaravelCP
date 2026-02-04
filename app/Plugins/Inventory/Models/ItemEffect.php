<?php

namespace App\Plugins\Inventory\Models;

use Illuminate\Database\Eloquent\Model;

class ItemEffect extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'type',
        'stat',
        'modifier_type',
    ];

    /**
     * Get all available effect types for dropdowns
     */
    public static function getEffectTypes(): array
    {
        return ['buff', 'debuff', 'instant', 'passive'];
    }

    /**
     * Get all available modifier types
     */
    public static function getModifierTypes(): array
    {
        return ['flat', 'percent'];
    }

    /**
     * Get all available stats
     */
    public static function getStats(): array
    {
        return [
            'health',
            'energy',
            'strength',
            'defense',
            'speed',
            'damage',
            'cooldown',
            'experience',
            'money',
            'crime_success',
            'jail_time',
            'hospital',
        ];
    }
}
