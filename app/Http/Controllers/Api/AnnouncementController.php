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
        $announcements = Announcement::where('active', true)
            ->where('starts_at', '<=', now())
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'announcements' => $announcements
        ]);
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
