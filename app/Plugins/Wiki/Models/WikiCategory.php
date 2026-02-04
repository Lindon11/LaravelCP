<?php

namespace App\Plugins\Wiki\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WikiCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'order',
    ];

    protected static function booted(): void
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function pages()
    {
        return $this->hasMany(WikiPage::class, 'category_id')->orderBy('order');
    }

    public function publishedPages()
    {
        return $this->hasMany(WikiPage::class, 'category_id')
            ->where('is_published', true)
            ->orderBy('order');
    }
}
