<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\User;
use App\Core\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserToolsController extends Controller
{
    protected ActivityLogService $activityService;

    public function __construct(ActivityLogService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Search for a user by username, email, or ID
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (!$query) {
            return response()->json(['success' => false, 'message' => 'Search query required'], 400);
        }

        $users = User::where('username', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('id', $query)
            ->select('id', 'username', 'email', 'level', 'created_at')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }

    /**
     * Get comprehensive user data for tools view
     */
    public function show(int $id)
    {
        $user = User::with(['currentRank', 'location', 'roles'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    /**
     * Get user's inventory items
     */
    public function inventory(int $id)
    {
        $user = User::findOrFail($id);

        $inventory = DB::table('user_inventories')
            ->join('items', 'user_inventories.item_id', '=', 'items.id')
            ->where('user_inventories.user_id', $id)
            ->select(
                'user_inventories.id',
                'user_inventories.quantity',
                'user_inventories.equipped',
                'user_inventories.created_at',
                'items.id as item_id',
                'items.name as item_name',
                'items.type as item_type',
                'items.codename as item_codename',
                'items.value as item_value'
            )
            ->orderBy('items.type')
            ->orderBy('items.name')
            ->get();

        return response()->json([
            'success' => true,
            'inventory' => $inventory,
            'total_items' => $inventory->sum('quantity'),
            'total_value' => $inventory->sum(fn($i) => $i->quantity * $i->item_value),
        ]);
    }

    /**
     * Get user's active timers/cooldowns
     */
    public function timers(int $id)
    {
        $user = User::findOrFail($id);

        // Get timers from user_timers table if it exists
        $timers = [];

        if (DB::getSchemaBuilder()->hasTable('user_timers')) {
            $timers = DB::table('user_timers')
                ->where('user_id', $id)
                ->where('expires_at', '>', now())
                ->select('id', 'type', 'expires_at', 'created_at', 'metadata')
                ->orderBy('expires_at')
                ->get();
        }

        // Also get cooldowns from the user record
        $userTimers = [];

        if ($user->crime_cooldown && $user->crime_cooldown > now()) {
            $userTimers[] = [
                'type' => 'crime',
                'expires_at' => $user->crime_cooldown,
                'source' => 'user_field',
            ];
        }
        if ($user->theft_cooldown && $user->theft_cooldown > now()) {
            $userTimers[] = [
                'type' => 'theft',
                'expires_at' => $user->theft_cooldown,
                'source' => 'user_field',
            ];
        }
        if ($user->gym_cooldown && $user->gym_cooldown > now()) {
            $userTimers[] = [
                'type' => 'gym',
                'expires_at' => $user->gym_cooldown,
                'source' => 'user_field',
            ];
        }
        if ($user->travel_arrival && $user->travel_arrival > now()) {
            $userTimers[] = [
                'type' => 'travel',
                'expires_at' => $user->travel_arrival,
                'source' => 'user_field',
            ];
        }
        if ($user->jail_until && $user->jail_until > now()) {
            $userTimers[] = [
                'type' => 'jail',
                'expires_at' => $user->jail_until,
                'source' => 'user_field',
            ];
        }
        if ($user->hospital_until && $user->hospital_until > now()) {
            $userTimers[] = [
                'type' => 'hospital',
                'expires_at' => $user->hospital_until,
                'source' => 'user_field',
            ];
        }

        return response()->json([
            'success' => true,
            'timers' => $timers,
            'user_timers' => $userTimers,
        ]);
    }

    /**
     * Clear a specific timer for a user
     */
    public function clearTimer(Request $request, int $id, string $timerType)
    {
        $user = User::findOrFail($id);

        // Map timer types to user fields
        $fieldMap = [
            'crime' => 'crime_cooldown',
            'theft' => 'theft_cooldown',
            'gym' => 'gym_cooldown',
            'travel' => 'travel_arrival',
            'jail' => 'jail_until',
            'hospital' => 'hospital_until',
        ];

        if (isset($fieldMap[$timerType])) {
            $user->update([$fieldMap[$timerType] => null]);
        }

        // Also clear from user_timers table if exists
        if (DB::getSchemaBuilder()->hasTable('user_timers')) {
            DB::table('user_timers')
                ->where('user_id', $id)
                ->where('type', $timerType)
                ->delete();
        }

        return response()->json([
            'success' => true,
            'message' => "Timer '{$timerType}' cleared for user",
        ]);
    }

    /**
     * Get user's activity/action history
     */
    public function activity(int $id)
    {
        $user = User::findOrFail($id);

        $activity = $this->activityService->getUserActivity($user, 100);

        // Group by type for summary
        $summary = collect($activity)->groupBy('type')->map->count();

        return response()->json([
            'success' => true,
            'activity' => $activity,
            'summary' => $summary,
        ]);
    }

    /**
     * Get user's flags/tags
     */
    public function flags(int $id)
    {
        $user = User::findOrFail($id);

        $flags = [];

        // Check various flag conditions
        if ($user->banned_until) {
            $flags[] = [
                'type' => 'banned',
                'label' => 'Banned',
                'value' => $user->banned_until,
                'reason' => $user->ban_reason,
                'severity' => 'danger',
            ];
        }

        if ($user->muted_until && $user->muted_until > now()) {
            $flags[] = [
                'type' => 'muted',
                'label' => 'Muted',
                'value' => $user->muted_until,
                'severity' => 'warning',
            ];
        }

        if ($user->is_verified) {
            $flags[] = [
                'type' => 'verified',
                'label' => 'Verified',
                'value' => true,
                'severity' => 'success',
            ];
        }

        if ($user->is_donator) {
            $flags[] = [
                'type' => 'donator',
                'label' => 'Donator',
                'value' => $user->donator_until ?? true,
                'severity' => 'info',
            ];
        }

        // Check for suspicious flags
        if (DB::getSchemaBuilder()->hasTable('user_flags')) {
            $userFlags = DB::table('user_flags')
                ->where('user_id', $id)
                ->get();

            foreach ($userFlags as $flag) {
                $flags[] = [
                    'type' => $flag->type,
                    'label' => $flag->label ?? ucfirst($flag->type),
                    'value' => $flag->value,
                    'reason' => $flag->reason ?? null,
                    'severity' => $flag->severity ?? 'info',
                    'created_at' => $flag->created_at,
                ];
            }
        }

        // Check roles for special flags
        foreach ($user->roles as $role) {
            if (in_array($role->name, ['admin', 'moderator', 'staff'])) {
                $flags[] = [
                    'type' => 'role',
                    'label' => ucfirst($role->name),
                    'value' => $role->name,
                    'severity' => 'info',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'flags' => $flags,
        ]);
    }

    /**
     * Add a flag to user
     */
    public function addFlag(Request $request, int $id)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'label' => 'nullable|string|max:100',
            'value' => 'nullable|string',
            'reason' => 'nullable|string|max:500',
            'severity' => 'nullable|in:info,success,warning,danger',
        ]);

        $user = User::findOrFail($id);

        // Create user_flags table if it doesn't exist
        if (!DB::getSchemaBuilder()->hasTable('user_flags')) {
            DB::statement('CREATE TABLE user_flags (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                type VARCHAR(50) NOT NULL,
                label VARCHAR(100),
                value TEXT,
                reason TEXT,
                severity VARCHAR(20) DEFAULT "info",
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX(user_id),
                INDEX(type)
            )');
        }

        DB::table('user_flags')->insert([
            'user_id' => $id,
            'type' => $validated['type'],
            'label' => $validated['label'] ?? ucfirst($validated['type']),
            'value' => $validated['value'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'severity' => $validated['severity'] ?? 'info',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Flag added',
        ]);
    }

    /**
     * Remove a flag from user
     */
    public function removeFlag(Request $request, int $id, string $flagType)
    {
        $user = User::findOrFail($id);

        // Handle built-in flags
        switch ($flagType) {
            case 'banned':
                $user->update(['banned_until' => null, 'ban_reason' => null]);
                break;
            case 'muted':
                $user->update(['muted_until' => null]);
                break;
        }

        // Remove from user_flags table
        if (DB::getSchemaBuilder()->hasTable('user_flags')) {
            DB::table('user_flags')
                ->where('user_id', $id)
                ->where('type', $flagType)
                ->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Flag removed',
        ]);
    }

    /**
     * Get job/task statistics for user
     */
    public function jobs(int $id)
    {
        $user = User::findOrFail($id);
        $jobs = [];

        // Get crime stats
        if (DB::getSchemaBuilder()->hasTable('crime_logs')) {
            $crimeStats = DB::table('crime_logs')
                ->where('user_id', $id)
                ->selectRaw('crime_id, COUNT(*) as total_completed')
                ->groupBy('crime_id')
                ->get();

            foreach ($crimeStats as $stat) {
                $crime = DB::table('crimes')->find($stat->crime_id);
                if ($crime) {
                    $jobs[] = [
                        'name' => $crime->name,
                        'codename' => $crime->codename ?? null,
                        'type' => 'crime',
                        'total_completed' => $stat->total_completed,
                    ];
                }
            }
        }

        // Get mission stats
        if (DB::getSchemaBuilder()->hasTable('user_missions')) {
            $missionStats = DB::table('user_missions')
                ->join('missions', 'user_missions.mission_id', '=', 'missions.id')
                ->where('user_missions.user_id', $id)
                ->where('user_missions.status', 'completed')
                ->selectRaw('missions.name, missions.codename, COUNT(*) as total_completed')
                ->groupBy('missions.id', 'missions.name', 'missions.codename')
                ->get();

            foreach ($missionStats as $stat) {
                $jobs[] = [
                    'name' => $stat->name,
                    'codename' => $stat->codename,
                    'type' => 'mission',
                    'total_completed' => $stat->total_completed,
                ];
            }
        }

        // Get organized crime stats
        if (DB::getSchemaBuilder()->hasTable('organized_crime_participants')) {
            $ocStats = DB::table('organized_crime_participants')
                ->join('organized_crimes', 'organized_crime_participants.organized_crime_id', '=', 'organized_crimes.id')
                ->where('organized_crime_participants.user_id', $id)
                ->where('organized_crimes.status', 'completed')
                ->selectRaw('COUNT(*) as total_completed')
                ->first();

            if ($ocStats && $ocStats->total_completed > 0) {
                $jobs[] = [
                    'name' => 'Organized Crimes',
                    'codename' => 'organized_crime',
                    'type' => 'organized_crime',
                    'total_completed' => $ocStats->total_completed,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'jobs' => $jobs,
        ]);
    }

    /**
     * Get detailed job history with timestamps
     */
    public function jobHistory(int $id, Request $request)
    {
        $user = User::findOrFail($id);
        $type = $request->get('type', 'all');
        $limit = $request->get('limit', 50);

        $history = [];

        // Get activity log entries for job-like activities
        $activityTypes = ['crime_attempt', 'organized_crime', 'theft_attempt', 'gym_train'];

        if ($type !== 'all') {
            $activityTypes = [$type];
        }

        $activities = DB::table('activity_logs')
            ->where('user_id', $id)
            ->whereIn('type', $activityTypes)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        foreach ($activities as $activity) {
            $metadata = json_decode($activity->metadata, true) ?? [];

            $history[] = [
                'id' => $activity->id,
                'type' => $activity->type,
                'name' => $metadata['crime_name'] ?? $metadata['name'] ?? ucfirst(str_replace('_', ' ', $activity->type)),
                'codename' => $metadata['codename'] ?? null,
                'success' => $metadata['success'] ?? null,
                'iterations' => 1,
                'time' => $activity->created_at,
                'metadata' => $metadata,
            ];
        }

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }
}
