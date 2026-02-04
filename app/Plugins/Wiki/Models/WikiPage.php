<?php

namespace App\Plugins\Wiki\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WikiPage extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'is_published',
        'order',
        'views',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(WikiCategory::class, 'category_id');
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }
}
