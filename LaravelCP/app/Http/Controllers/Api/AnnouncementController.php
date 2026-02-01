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
        // Return empty array for now - announcements table structure needs review
        return response()->json([
            'announcements' => []
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
