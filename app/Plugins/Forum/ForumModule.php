<?php

namespace App\Plugins\Forum;

use App\Plugins\Plugin;

/**
 * Forum Module
 *
 * Handles community forum system for player discussions
 * Provides categories, topics, and replies
 */
class ForumModule extends Plugin
{
    protected string $name = 'Forum';

    public function construct(): void
    {
        $this->config = [
            'min_post_length' => 10,
            'max_post_length' => 5000,
            'posts_per_page' => 20,
            'topics_per_page' => 25,
        ];
    }

    /**
     * Get all forum categories
     */
    public function getCategories(): array
    {
        $categories = \App\Plugins\Forum\Models\ForumCategory::withCount(['topics', 'posts'])
            ->orderBy('order')
            ->get();

        return $this->applyModuleHook('alterForumCategories', [
            'categories' => $categories,
        ]);
    }

    /**
     * Get topics for a category
     */
    public function getTopicsByCategory($category): array
    {
        $topics = $category->topics()
            ->with(['author', 'lastPost.author'])
            ->withCount('posts')
            ->orderBy('pinned', 'desc')
            ->orderBy('last_post_at', 'desc')
            ->paginate($this->config['topics_per_page']);

        return $this->applyModuleHook('alterForumTopics', [
            'topics' => $topics,
            'category' => $category,
        ]);
    }

    /**
     * Get module stats for sidebar
     */
    public function getStats(?\App\Core\Models\User $user = null): array
    {
        return [
            'topics' => \App\Plugins\Forum\Models\ForumTopic::count(),
            'posts' => \App\Plugins\Forum\Models\ForumPost::count(),
            'categories' => \App\Plugins\Forum\Models\ForumCategory::count(),
        ];
    }
}
