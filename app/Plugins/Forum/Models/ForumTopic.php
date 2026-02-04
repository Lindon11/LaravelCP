<?php

namespace App\Plugins\Forum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumTopic extends Model
{
    protected $fillable = ['category_id', 'user_id', 'title', 'locked', 'sticky', 'views'];

    protected $casts = [
        'locked' => 'boolean',
        'sticky' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class, 'topic_id');
    }
}
