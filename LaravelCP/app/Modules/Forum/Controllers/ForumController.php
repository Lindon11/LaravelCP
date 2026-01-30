<?php

namespace App\Modules\Forum\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\User;
use App\Services\ForumService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForumController extends Controller
{
    public function __construct(
        protected ForumService $forumService
    ) {}

    public function index()
    {
        $categories = $this->forumService->getCategories();
        
        return Inertia::render('Modules/Forum/Index', [
            'categories' => $categories,
        ]);
    }

    public function category(ForumCategory $category)
    {
        $topics = $this->forumService->getTopicsByCategory($category);
        
        return Inertia::render('Modules/Forum/Category', [
            'category' => $category,
            'topics' => $topics,
        ]);
    }

    public function topic(ForumTopic $topic)
    {
        $data = $this->forumService->getTopic($topic);
        
        return Inertia::render('Modules/Forum/Topic', [
            'topic' => $data['topic'],
            'posts' => $data['posts'],
        ]);
    }

    public function createTopic(Request $request, ForumCategory $category)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
        ]);

        $player = User::where('user_id', $request->user()->id)->firstOrFail();
        
        $topic = $this->forumService->createTopic($player, $category, $request->title, $request->content);
        
        return redirect()->route('forum.topic', $topic)->with('success', 'Topic created successfully');
    }

    public function reply(Request $request, ForumTopic $topic)
    {
        $request->validate([
            'content' => 'required|string|min:10',
        ]);

        $player = User::where('user_id', $request->user()->id)->firstOrFail();
        
        try {
            $this->forumService->replyToTopic($player, $topic, $request->content);
            return back()->with('success', 'Reply posted successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
