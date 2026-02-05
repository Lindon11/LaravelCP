<?php

namespace App\Plugins\Forum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ForumCategory extends Model
{
    protected $fillable = ['name', 'description', 'order'];

    public function topics(): HasMany
    {
        return $this->hasMany(ForumTopic::class, 'category_id');
    }

    public function posts(): HasManyThrough
    {
        return $this->hasManyThrough(ForumPost::class, ForumTopic::class, 'category_id', 'topic_id');
    }
}
