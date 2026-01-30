<?php

namespace App\Services;

use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\ForumPost;
use App\Models\User;

class ForumService
{
    public function getCategories()
    {
        return ForumCategory::orderBy('order')->orderBy('name')->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'topic_count' => $category->topics()->count(),
                'post_count' => ForumPost::whereIn('topic_id', $category->topics()->pluck('id'))->count(),
            ];
        });
    }

    public function getTopicsByCategory(ForumCategory $category)
    {
        return $category->topics()
            ->with(['author', 'posts'])
            ->orderByDesc('sticky')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($topic) {
                return [
                    'id' => $topic->id,
                    'title' => $topic->title,
                    'author' => $topic->author->username,
                    'author_id' => $topic->author_id,
                    'locked' => $topic->locked,
                    'sticky' => $topic->sticky,
                    'views' => $topic->views,
                    'replies' => $topic->posts()->count(),
                    'created_at' => $topic->created_at->diffForHumans(),
                    'updated_at' => $topic->updated_at->diffForHumans(),
                ];
            });
    }

    public function getTopic(ForumTopic $topic)
    {
        $topic->increment('views');
        
        return [
            'topic' => [
                'id' => $topic->id,
                'title' => $topic->title,
                'locked' => $topic->locked,
                'sticky' => $topic->sticky,
                'views' => $topic->views,
                'author' => $topic->author->username,
                'author_id' => $topic->author_id,
                'created_at' => $topic->created_at->format('M d, Y H:i'),
            ],
            'posts' => $topic->posts()->with('author')->orderBy('created_at')->get()->map(function ($post) {
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'author' => $post->author->username,
                    'author_id' => $post->author_id,
                    'author_level' => $post->author->level,
                    'created_at' => $post->created_at->format('M d, Y H:i'),
                ];
            }),
        ];
    }

    public function createTopic(User $player, ForumCategory $category, string $title, string $content): ForumTopic
    {
        $topic = ForumTopic::create([
            'category_id' => $category->id,
            'user_id' => $player->id,
            'title' => $title,
        ]);

        ForumPost::create([
            'topic_id' => $topic->id,
            'user_id' => $player->id,
            'content' => $content,
        ]);

        return $topic;
    }

    public function replyToTopic(User $player, ForumTopic $topic, string $content): ForumPost
    {
        if ($topic->locked) {
            throw new \Exception('This topic is locked.');
        }

        $post = ForumPost::create([
            'topic_id' => $topic->id,
            'user_id' => $player->id,
            'content' => $content,
        ]);

        $topic->touch();

        return $post;
    }
}
