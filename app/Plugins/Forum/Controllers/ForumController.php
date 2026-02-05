<?php

namespace App\Plugins\Forum\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Forum\Models\ForumCategory;
use App\Plugins\Forum\Models\ForumTopic;
use App\Core\Models\User;
use App\Plugins\Forum\Services\ForumService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ForumController extends Controller
{
    public function __construct(
        protected ForumService $forumService
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->forumService->getCategories();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = ForumCategory::withCount(['topics', 'posts'])
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function category(Request $request, ForumCategory $category): JsonResponse
    {
        $search = $request->get('search');
        $perPage = (int) $request->get('per_page', 20);

        $topics = $this->forumService->getTopicsByCategory($category, $search, $perPage);

        return response()->json([
            'category' => $category,
            'topics' => $topics,
        ]);
    }

    public function topic(Request $request, ForumTopic $topic): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);

        // Update service to accept per-page in the future; for now service paginates posts at fixed size.
        $data = $this->forumService->getTopic($topic);

        return response()->json([
            'topic' => $data['topic'],
            'posts' => $data['posts'],
        ]);
    }

    public function createTopic(Request $request, ForumCategory $category): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
        ]);

        $user = $request->user();

        $topic = $this->forumService->createTopic($user, $category, $request->title, $request->content);

        return response()->json([
            'message' => 'Topic created successfully',
            'topic' => $topic
        ], 201);
    }

    public function reply(Request $request, ForumTopic $topic): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|min:10',
        ]);

        $user = $request->user();

        try {
            $post = $this->forumService->replyToTopic($user, $topic, $request->content);
            return response()->json([
                'message' => 'Reply posted successfully',
                'post' => $post
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
