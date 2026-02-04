<?php

namespace App\Plugins\Drugs\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerDrug extends Model
{
    protected $fillable = [
        'user_id',
        'drug_id',
        'quantity',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }
}
