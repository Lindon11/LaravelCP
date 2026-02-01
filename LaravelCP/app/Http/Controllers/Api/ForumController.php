<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Services\ForumService;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function __construct(
        protected ForumService $forumService
    ) {}

    /**
     * Get forum categories
     */
    public function index(Request $request)
    {
        $categories = $this->forumService->getCategories();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Get topics in a category
     */
    public function category(ForumCategory $category)
    {
        $topics = $this->forumService->getTopicsByCategory($category);

        return response()->json([
            'category' => $category,
            'topics' => $topics,
        ]);
    }

    /**
     * Get a topic with posts
     */
    public function topic(ForumTopic $topic)
    {
        $data = $this->forumService->getTopic($topic);

        return response()->json([
            'topic' => $data['topic'],
            'posts' => $data['posts'],
        ]);
    }

    /**
     * Create a topic
     */
    public function createTopic(Request $request, ForumCategory $category)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
        ]);

        $player = $request->user();

        try {
            $topic = $this->forumService->createTopic($player, $category, $request->title, $request->content);
            return response()->json([
                'success' => true,
                'message' => 'Topic created successfully',
                'topic' => $topic,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reply to a topic
     */
    public function reply(Request $request, ForumTopic $topic)
    {
        $request->validate([
            'content' => 'required|string|min:2',
        ]);

        $player = $request->user();

        try {
            $post = $this->forumService->createPost($player, $topic, $request->content);
            return response()->json([
                'success' => true,
                'message' => 'Reply posted successfully',
                'post' => $post,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
