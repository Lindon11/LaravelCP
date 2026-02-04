<?php

namespace App\Plugins\Alliances\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Plugins\Gang\Models\Gang;

class AllianceMember extends Model
{
    protected $fillable = [
        'alliance_id',
        'gang_id',
        'role',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    /**
     * The alliance
     */
    public function alliance(): BelongsTo
    {
        return $this->belongsTo(Alliance::class);
    }

    /**
     * The gang
     */
    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class);
    }
}
