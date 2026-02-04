<?php

namespace App\Plugins\Announcements;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Plugins\Announcements\Models\Announcement;
use App\Core\Services\NotificationService;
use Illuminate\Support\Collection;

/**
 * Announcements Module
 * 
 * Handles system announcements and news
 */
class AnnouncementsModule extends Plugin
{
    protected string $name = 'Announcements';
    protected ?NotificationService $notificationService = null;
    
    public function construct(): void
    {
        $this->config = [
            'default_type' => 'info',
            'types' => ['info', 'warning', 'success', 'danger', 'update', 'event'],
            'targets' => ['all', 'level_range', 'location'],
        ];
        
        // Inject notification service if available
        if (app()->bound(NotificationService::class)) {
            $this->notificationService = app(NotificationService::class);
        }
    }
    
    /**
     * Get active announcements for a player
     */
    public function getActiveAnnouncements(?User $user = null): Collection
    {
        $query = Announcement::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
        
        // Apply user-specific targeting
        if ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('target', 'all')
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('target', 'level_range')
                            ->where(function ($q3) use ($user) {
                                $q3->whereNull('min_level')
                                    ->orWhere('min_level', '<=', $user->level);
                            })
                            ->where(function ($q3) use ($user) {
                                $q3->whereNull('max_level')
                                    ->orWhere('max_level', '>=', $user->level);
                            });
                    })
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('target', 'location')
                            ->where('location_id', $user->location_id);
                    });
            });
        }
        
        $announcements = $query->orderBy('is_sticky', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Apply filter hook
        $filterResult = $this->applyModuleHook('filterAnnouncements', [
            'announcements' => $announcements,
            'user' => $user,
        ]);
        
        return $filterResult['announcements'] ?? $announcements;
    }
    
    /**
     * Create an announcement (admin)
     */
    public function create(User $admin, array $data): Announcement
    {
        $announcement = Announcement::create([
            'created_by' => $admin->id,
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? $this->config['default_type'],
            'target' => $data['target'] ?? 'all',
            'min_level' => $data['min_level'] ?? null,
            'max_level' => $data['max_level'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'published_at' => $data['published_at'] ?? now(),
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'is_sticky' => $data['is_sticky'] ?? false,
        ]);
        
        $this->applyModuleHook('OnAnnouncementCreated', [
            'announcement' => $announcement,
            'admin' => $admin,
        ]);
        
        // Notify users if active
        if ($announcement->is_active && $this->notificationService) {
            $this->notifyUsers($announcement);
        }

        return $announcement;
    }
    
    /**
     * Notify users about announcement
     */
    public function notifyUsers(Announcement $announcement): void
    {
        if (!$this->notificationService) {
            return;
        }
        
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
    
    /**
     * Mark announcement as viewed
     */
    public function markViewed(User $user, Announcement $announcement): void
    {
        // Could implement user-specific view tracking here
        $this->applyModuleHook('OnAnnouncementViewed', [
            'announcement' => $announcement,
            'user' => $user,
        ]);
    }
    
    /**
     * Update an announcement (admin)
     */
    public function update(Announcement $announcement, array $data): Announcement
    {
        $announcement->update($data);
        return $announcement->fresh();
    }
    
    /**
     * Delete an announcement (admin)
     */
    public function delete(Announcement $announcement): bool
    {
        return $announcement->delete();
    }
    
    /**
     * Get all announcements for admin
     */
    public function getAllAnnouncements()
    {
        return Announcement::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }
    
    /**
     * Get announcement by ID
     */
    public function getAnnouncement(int $id): Announcement
    {
        return Announcement::findOrFail($id);
    }
}
