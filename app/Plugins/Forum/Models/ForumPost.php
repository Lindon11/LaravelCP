<?php

namespace App\Plugins\Forum\Models;

use App\Core\Models\User;
use App\Core\Facades\TextFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumPost extends Model
{
    protected $fillable = ['topic_id', 'user_id', 'content'];

    protected $appends = ['formatted_content'];

    /**
     * Get the formatted content with BBCode and emojis parsed
     */
    public function getFormattedContentAttribute(): string
    {
        return TextFormatter::format($this->content ?? '');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
