<?php

namespace App\Services;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;

class AnnouncementService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function create(User $admin, array $data): Announcement
    {
        $announcement = Announcement::create([
            'created_by' => $admin->id,
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? 'info',
            'target' => $data['target'] ?? 'all',
            'min_level' => $data['min_level'] ?? null,
            'max_level' => $data['max_level'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'published_at' => $data['published_at'] ?? now(),
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'is_sticky' => $data['is_sticky'] ?? false,
        ]);

        // Create notifications for all users who can see this announcement
        if ($announcement->is_active) {
            $this->notifyUsers($announcement);
        }

        return $announcement;
    }

    /**
     * Create notifications for users about a new announcement
     */
    public function notifyUsers(Announcement $announcement): void
    {
        $query = User::query();

        // Apply targeting filters
        if ($announcement->target !== 'all') {
            switch ($announcement->target) {
                case 'level_range':
                    if ($announcement->min_level) {
                        $query->where('level', '>=', $announcement->min_level);
                    }
                    if ($announcement->max_level) {
                        $query->where('level', '<=', $announcement->max_level);
                    }
                    break;
                case 'location':
                    if ($announcement->location_id) {
                        $query->where('location_id', $announcement->location_id);
                    }
                    break;
            }
        }

        // Get users in chunks to avoid memory issues
        $query->chunk(100, function ($users) use ($announcement) {
            foreach ($users as $user) {
                $this->notificationService->announcement(
                    $user,
                    $announcement->title,
                    $announcement->message
                );
            }
        });
    }
    
    public function getActiveAnnouncements(User $player): Collection
    {
        return Announcement::active()
            ->orderBy('is_sticky', 'desc')
            ->orderBy('published_at', 'desc')
            ->get()
            ->filter(fn($announcement) => $announcement->isVisibleTo($player));
    }
    
    public function incrementViews(Announcement $announcement): void
    {
        $announcement->increment('views');
    }
    
    public function sendMassEmail(array $data): array
    {
        $query = User::query()->with('user');
        // Apply filters
        if (isset($data['target']) && $data['target'] !== 'all') {
            switch ($data['target']) {
                case 'level_range':
                    if (isset($data['min_level'])) {
                        $query->where('level', '>=', $data['min_level']);
                    }
                    if (isset($data['max_level'])) {
                        $query->where('level', '<=', $data['max_level']);
                    }
                    break;
                case 'location':
                    if (isset($data['location_id'])) {
                        $query->where('location_id', $data['location_id']);
                    }
                    break;
                case 'active':
                    $query->where('last_active', '>=', now()->subDays(7));
                    break;
                case 'inactive':
                    $query->where('last_active', '<', now()->subDays(30));
                    break;
            }
        }
        $players = $query->get();
        $sent = 0;
        $failed = 0;
        foreach ($players as $player) {
            try {
                if ($player->user && $player->user->email) {
                    // In production, queue this
                    Mail::raw($data['message'], function ($message) use ($player, $data) {
                        $message->to($player->user->email)
                            ->subject($data['subject']);
                    });
                    $sent++;
                }
            } catch (\Exception $e) {
                $failed++;
            }
        }
        
        return [
            'total' => $players->count(),
            'sent' => $sent,
            'failed' => $failed,
        ];
    }
}
