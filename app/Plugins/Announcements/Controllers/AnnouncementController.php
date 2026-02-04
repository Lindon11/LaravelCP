<?php

namespace App\Plugins\Announcements\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Announcements\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('creator')
            ->orderBy('is_sticky', 'desc')
            ->orderBy('published_at', 'desc')
            ->get();
        return response()->json($announcements);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'created_by' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:news,event,maintenance,update,alert',
            'target' => 'required|in:all,level_range,location',
            'min_level' => 'nullable|integer|min:1',
            'max_level' => 'nullable|integer|min:1',
            'location_id' => 'nullable|exists:locations,id',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
            'is_active' => 'boolean',
            'is_sticky' => 'boolean',
        ]);

        $announcement = Announcement::create($validated);
        return response()->json($announcement->load('creator'), 201);
    }

    public function show(Announcement $announcement)
    {
        return response()->json($announcement->load('creator'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'created_by' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:news,event,maintenance,update,alert',
            'target' => 'required|in:all,level_range,location',
            'min_level' => 'nullable|integer|min:1',
            'max_level' => 'nullable|integer|min:1',
            'location_id' => 'nullable|exists:locations,id',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
            'is_active' => 'boolean',
            'is_sticky' => 'boolean',
        ]);

        $announcement->update($validated);
        return response()->json($announcement->load('creator'));
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return response()->json(null, 204);
    }
}
