<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\User;
use App\Core\Models\PlayerBan;
use App\Core\Models\IpBan;
use App\Core\Models\Item;
use App\Core\Services\ModerationService;
use App\Plugins\Announcements\Services\AnnouncementService;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function __construct(
        protected ModerationService $moderationService,
        protected AnnouncementService $announcementService
    ) {
    }

    public function index()
    {
        $recentBans = PlayerBan::with(['user', 'bannedBy'])
            ->latest()
            ->take(10)
            ->get();

        $activeBans = PlayerBan::active()
            ->with(['user', 'bannedBy'])
            ->count();

        $activeIpBans = IpBan::active()->count();

        return response()->json([
            'recentBans' => $recentBans,
            'stats' => [
                'active_bans' => $activeBans,
                'active_ip_bans' => $activeIpBans,
            ],
        ]);
    }

    public function banPlayer(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:players,id',
            'type' => 'required|in:temporary,permanent',
            'reason' => 'required|string|max:500',
            'duration_hours' => 'required_if:type,temporary|nullable|integer|min:1',
        ]);

        try {
            $player = User::findOrFail($request->user_id);
            
            $banData = [
                'type' => $request->type,
                'reason' => $request->reason,
            ];

            if ($request->type === 'temporary') {
                $banData['expires_at'] = now()->addHours($request->duration_hours);
            }

            $this->moderationService->banPlayer($player, $request->user(), $banData);

            return back()->with('success', "Player {$player->name} has been banned.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to ban player: ' . $e->getMessage());
        }
    }

    public function unbanPlayer(Request $request, PlayerBan $ban)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->moderationService->unbanPlayer($ban, $request->user(), $request->reason);

            return back()->with('success', 'Player has been unbanned.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to unban player: ' . $e->getMessage());
        }
    }

    public function warnPlayer(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:players,id',
            'severity' => 'required|in:minor,moderate,severe',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $player = User::findOrFail($request->user_id);

            $this->moderationService->warnPlayer($player, $request->user(), [
                'severity' => $request->severity,
                'reason' => $request->reason,
            ]);

            return back()->with('success', "Warning issued to {$player->name}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to issue warning: ' . $e->getMessage());
        }
    }

    public function adjustStats(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:players,id',
            'cash' => 'nullable|integer',
            'bank' => 'nullable|integer',
            'respect' => 'nullable|integer',
            'experience' => 'nullable|integer',
            'health' => 'nullable|integer|min:0|max:100',
            'level' => 'nullable|integer|min:1',
        ]);

        try {
            $player = User::findOrFail($request->user_id);

            $adjustments = $request->only(['cash', 'bank', 'respect', 'experience', 'health', 'level']);
            $this->moderationService->adjustPlayerStats($player, $adjustments);

            return back()->with('success', "Player stats adjusted successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to adjust stats: ' . $e->getMessage());
        }
    }

    public function grantItem(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:players,id',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1|max:1000',
        ]);

        try {
            $player = User::findOrFail($request->user_id);
            
            $this->moderationService->grantItem($player, $request->item_id, $request->quantity);

            $item = Item::find($request->item_id);
            return back()->with('success', "Granted {$request->quantity}x {$item->name} to {$player->name}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to grant item: ' . $e->getMessage());
        }
    }

    public function sendAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
            'type' => 'required|in:info,warning,success,danger',
            'target' => 'required|in:all,online,level_range,location',
            'min_level' => 'nullable|integer|min:1',
            'max_level' => 'nullable|integer|min:1',
            'location_id' => 'nullable|exists:locations,id',
            'expires_at' => 'nullable|date|after:now',
            'is_sticky' => 'boolean',
        ]);

        try {
            $this->announcementService->create($request->user(), $request->all());

            return back()->with('success', 'Announcement created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create announcement: ' . $e->getMessage());
        }
    }

    public function sendMassEmail(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
            'target' => 'required|in:all,level_range,location,active,inactive',
            'min_level' => 'nullable|integer|min:1',
            'max_level' => 'nullable|integer|min:1',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        try {
            $result = $this->announcementService->sendMassEmail($request->all());

            return back()->with('success', "Mass email queued. Sent: {$result['sent']}, Failed: {$result['failed']}");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send mass email: ' . $e->getMessage());
        }
    }
}

