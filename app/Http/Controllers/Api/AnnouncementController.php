<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Get active announcements for the player
     */
    public function index(Request $request)
    {
        // Get active announcements
        $announcements = Announcement::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->orderBy('is_sticky', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($announcements); // Return array directly
    }

    /**
     * Mark announcement as viewed by user
     */
    public function markViewed(Request $request, Announcement $announcement)
    {
        // Track that user has viewed this announcement
        // You can implement user-specific tracking if needed
        
        return response()->json([
            'success' => true
        ]);
    }
}
