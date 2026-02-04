<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    /**
     * List all users with filters and pagination
     */
    public function index(Request $request)
    {
        $query = User::with(['currentRank', 'location', 'roles']);

        // Search filter
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('username', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        // Rank filter
        if ($request->rank_id) {
            $query->where('rank_id', $request->rank_id);
        }

        // Status filter
        if ($request->status === 'banned') {
            $query->whereNotNull('banned_until');
        } elseif ($request->status === 'active') {
            $query->whereNull('banned_until');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $users = $query->paginate($request->get('per_page', 25));

        return response()->json($users);
    }

    /**
     * Get single user details
     */
    public function show($id)
    {
        $user = User::with([
            'currentRank',
            'location',
            'roles.permissions',
            'permissions'
        ])->findOrFail($id);

        return response()->json($user);
    }

    /**
     * Create new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'rank_id' => 'nullable|exists:ranks,id',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'name' => $validated['name'],
            'password' => Hash::make($validated['password']),
            'rank_id' => $validated['rank_id'] ?? 1,
            'location_id' => $validated['location_id'] ?? 1,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'nerve' => 100,
            'max_nerve' => 100,
            'cash' => 1000,
            'bank' => 0,
            'bullets' => 50,
            'experience' => 0,
            'level' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user->load('currentRank', 'location')
        ], 201);
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => 'sometimes|string|max:255|unique:users,username,' . $id,
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'name' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:8',
            'rank_id' => 'sometimes|exists:ranks,id',
            'location_id' => 'sometimes|exists:locations,id',
            'cash' => 'sometimes|numeric|min:0',
            'bank' => 'sometimes|numeric|min:0',
            'experience' => 'sometimes|integer|min:0',
            'level' => 'sometimes|integer|min:1',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user->fresh()->load('currentRank', 'location')
        ]);
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Ban user
     */
    public function ban(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'duration' => 'required|string|in:1day,3days,7days,30days,permanent',
            'notes' => 'nullable|string'
        ]);

        $user = User::findOrFail($id);

        $bannedUntil = match($validated['duration']) {
            '1day' => now()->addDay(),
            '3days' => now()->addDays(3),
            '7days' => now()->addDays(7),
            '30days' => now()->addDays(30),
            'permanent' => now()->addYears(100),
        };

        $user->update([
            'banned_until' => $bannedUntil,
            'ban_reason' => $validated['reason']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User banned successfully',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Unban user
     */
    public function unban($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'banned_until' => null,
            'ban_reason' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User unbanned successfully',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Get user statistics
     */
    public function statistics()
    {
        $stats = [
            'total_users' => User::count(),
            'active_today' => User::where('last_active', '>=', now()->subDay())->count(),
            'active_week' => User::where('last_active', '>=', now()->subWeek())->count(),
            'banned' => User::whereNotNull('banned_until')->count(),
            'new_today' => User::whereDate('created_at', today())->count(),
            'new_week' => User::where('created_at', '>=', now()->subWeek())->count(),
        ];

        return response()->json($stats);
    }
}
