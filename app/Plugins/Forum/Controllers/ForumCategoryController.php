<?php

namespace App\Plugins\Forum\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Forum\Models\ForumCategory;
use App\Plugins\Forum\Models\ForumTopic;
use App\Plugins\Forum\Models\ForumPost;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ForumCategoryController extends Controller
{
    /**
     * Display a listing of forum categories.
     */
    public function index(): JsonResponse
    {
        $categories = ForumCategory::withCount(['topics', 'posts'])
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    /**
     * Store a newly created forum category.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0'
        ]);

        $category = ForumCategory::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'order' => $validated['order'] ?? 0
        ]);

        return response()->json([
            'message' => 'Forum category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Display the specified forum category.
     */
    public function show(ForumCategory $category): JsonResponse
    {
        $category->loadCount(['topics', 'posts']);

        return response()->json($category);
    }

    /**
     * Update the specified forum category.
     */
    public function update(Request $request, ForumCategory $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0'
        ]);

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'order' => $validated['order'] ?? $category->order
        ]);

        return response()->json([
            'message' => 'Forum category updated successfully',
            'category' => $category
        ]);
    }

    /**
     * Remove the specified forum category.
     */
    public function destroy(ForumCategory $category): JsonResponse
    {
        // Check if category has topics
        if ($category->topics()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with existing topics. Delete or move topics first.'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Forum category deleted successfully'
        ]);
    }

    /**
     * Get all forum topics (admin).
     */
    public function topics(Request $request): JsonResponse
    {
        $query = ForumTopic::with(['category', 'author'])
            ->withCount('posts');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $topics = $query->orderBy('sticky', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($topics);
    }

    /**
     * Delete a forum topic.
     */
    public function destroyTopic(ForumTopic $topic): JsonResponse
    {
        // Delete all posts in the topic first
        $topic->posts()->delete();
        $topic->delete();

        return response()->json([
            'message' => 'Topic deleted successfully'
        ]);
    }

    /**
     * Toggle topic lock status.
     */
    public function toggleLock(ForumTopic $topic): JsonResponse
    {
        $topic->update(['locked' => !$topic->locked]);

        return response()->json([
            'message' => $topic->locked ? 'Topic locked' : 'Topic unlocked',
            'locked' => $topic->locked
        ]);
    }

    /**
     * Toggle topic sticky status.
     */
    public function toggleSticky(ForumTopic $topic): JsonResponse
    {
        $topic->update(['sticky' => !$topic->sticky]);

        return response()->json([
            'message' => $topic->sticky ? 'Topic stickied' : 'Topic unstickied',
            'sticky' => $topic->sticky
        ]);
    }

    /**
     * Delete a forum post.
     */
    public function destroyPost(ForumPost $post): JsonResponse
    {
        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ]);
    }
}
