<?php

namespace App\Plugins\Announcements\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Announcements\AnnouncementsModule;
use App\Plugins\Announcements\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementsController extends Controller
{
    protected AnnouncementsModule $module;
    
    public function __construct()
    {
        $this->module = new AnnouncementsModule();
    }
    
    /**
     * Get active announcements for player
     */
    public function index(Request $request)
    {
        $announcements = $this->module->getActiveAnnouncements($request->user());
        return response()->json($announcements);
    }
    
    /**
     * Mark announcement as viewed
     */
    public function markViewed(Request $request, Announcement $announcement)
    {
        $this->module->markViewed($request->user(), $announcement);
        return response()->json(['success' => true]);
    }
}
