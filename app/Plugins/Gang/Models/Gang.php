<?php

namespace App\Plugins\Gang\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gang extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tag',
        'description',
        'logo',
        'leader_id',
        'bank',
        'respect',
        'max_members',
        'level',
    ];

    protected $casts = [
        'bank' => 'integer',
        'respect' => 'integer',
        'max_members' => 'integer',
        'level' => 'integer',
    ];

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'gang_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(GangPermission::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GangLog::class);
    }

    public function getMemberCount(): int
    {
        return $this->members()->count();
    }
}
