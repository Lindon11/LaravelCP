<?php

namespace App\Plugins\Alliances\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Alliances\Models\Alliance;
use App\Plugins\Alliances\Models\AllianceMember;
use App\Plugins\Alliances\Models\Territory;
use App\Plugins\Alliances\Models\AllianceWar;
use Illuminate\Http\Request;

class AlliancesController extends Controller
{
    /**
     * List all alliances
     */
    public function index()
    {
        $alliances = Alliance::withCount('gangs')
            ->with('leader:id,name')
            ->orderBy('power', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'alliances' => $alliances,
        ]);
    }

    /**
     * Get alliance details
     */
    public function show(int $id)
    {
        $alliance = Alliance::with(['gangs.gang:id,name,level,power', 'territories', 'leader:id,name'])
            ->withCount('gangs')
            ->find($id);

        if (!$alliance) {
            return response()->json(['success' => false, 'message' => 'Alliance not found'], 404);
        }

        $activeWars = AllianceWar::where(function ($q) use ($id) {
            $q->where('attacker_id', $id)->orWhere('defender_id', $id);
        })->active()->with(['attacker:id,name', 'defender:id,name'])->get();

        return response()->json([
            'success' => true,
            'alliance' => $alliance,
            'wars' => $activeWars,
        ]);
    }

    /**
     * Create alliance (gang leader only)
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:alliances,name',
            'tag' => 'required|string|max:5|unique:alliances,tag',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
        ]);

        $user = auth()->user();

        // Check if user is a gang leader
        $gang = $user->gang;
        if (!$gang || $gang->leader_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'You must be a gang leader'], 400);
        }

        // Check if gang is already in an alliance
        $existing = AllianceMember::where('gang_id', $gang->id)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Your gang is already in an alliance'], 400);
        }

        // Check cost
        if ($gang->bank < 100000) {
            return response()->json(['success' => false, 'message' => 'Not enough gang funds (100,000 required)'], 400);
        }

        $gang->decrement('bank', 100000);

        $alliance = Alliance::create([
            'name' => $validated['name'],
            'tag' => strtoupper($validated['tag']),
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#808080',
            'leader_gang_id' => $gang->id,
            'created_by' => $user->id,
            'power' => $gang->power ?? 0,
            'treasury' => 0,
        ]);

        AllianceMember::create([
            'alliance_id' => $alliance->id,
            'gang_id' => $gang->id,
            'role' => 'leader',
            'joined_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alliance created',
            'alliance' => $alliance,
        ]);
    }

    /**
     * Invite a gang to alliance
     */
    public function invite(Request $request, int $id)
    {
        $validated = $request->validate([
            'gang_id' => 'required|exists:gangs,id',
        ]);

        $alliance = Alliance::find($id);
        if (!$alliance) {
            return response()->json(['success' => false, 'message' => 'Alliance not found'], 404);
        }

        // Check if user's gang is the leader
        $user = auth()->user();
        if (!$user->gang || $alliance->leader_gang_id !== $user->gang->id) {
            return response()->json(['success' => false, 'message' => 'Only alliance leader can invite'], 403);
        }

        // Check member limit
        if ($alliance->gangs()->count() >= 5) {
            return response()->json(['success' => false, 'message' => 'Alliance is full'], 400);
        }

        // Check if gang is already in an alliance
        $existing = AllianceMember::where('gang_id', $validated['gang_id'])->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Gang is already in an alliance'], 400);
        }

        // TODO: Create invitation system
        AllianceMember::create([
            'alliance_id' => $id,
            'gang_id' => $validated['gang_id'],
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $alliance->updatePower();

        return response()->json([
            'success' => true,
            'message' => 'Gang invited to alliance',
        ]);
    }

    /**
     * Leave alliance
     */
    public function leave(int $id)
    {
        $user = auth()->user();
        $gang = $user->gang;

        if (!$gang) {
            return response()->json(['success' => false, 'message' => 'You are not in a gang'], 400);
        }

        $membership = AllianceMember::where('alliance_id', $id)
            ->where('gang_id', $gang->id)
            ->first();

        if (!$membership) {
            return response()->json(['success' => false, 'message' => 'Gang is not in this alliance'], 404);
        }

        $alliance = Alliance::find($id);

        // Cannot leave if leader
        if ($alliance->leader_gang_id === $gang->id) {
            return response()->json(['success' => false, 'message' => 'Leader cannot leave, transfer leadership first'], 400);
        }

        $membership->delete();
        $alliance->updatePower();

        return response()->json([
            'success' => true,
            'message' => 'Left the alliance',
        ]);
    }

    /**
     * Get territories
     */
    public function territories()
    {
        $territories = Territory::with(['alliance:id,name,tag', 'owner:id,name'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'territories' => $territories,
        ]);
    }

    /**
     * Attack territory
     */
    public function attackTerritory(Request $request, int $territoryId)
    {
        $territory = Territory::find($territoryId);

        if (!$territory) {
            return response()->json(['success' => false, 'message' => 'Territory not found'], 404);
        }

        $user = auth()->user();
        $gang = $user->gang;

        if (!$gang) {
            return response()->json(['success' => false, 'message' => 'You must be in a gang'], 400);
        }

        $membership = AllianceMember::where('gang_id', $gang->id)->first();
        if (!$membership) {
            return response()->json(['success' => false, 'message' => 'Your gang must be in an alliance'], 400);
        }

        if ($territory->alliance_id === $membership->alliance_id) {
            return response()->json(['success' => false, 'message' => 'Cannot attack your own territory'], 400);
        }

        // TODO: Implement territory battle system

        return response()->json([
            'success' => true,
            'message' => 'Territory attack initiated',
        ]);
    }

    /**
     * Declare war on another alliance
     */
    public function declareWar(Request $request, int $id)
    {
        $validated = $request->validate([
            'target_alliance_id' => 'required|exists:alliances,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $alliance = Alliance::find($id);
        if (!$alliance) {
            return response()->json(['success' => false, 'message' => 'Alliance not found'], 404);
        }

        $user = auth()->user();
        if (!$user->gang || $alliance->leader_gang_id !== $user->gang->id) {
            return response()->json(['success' => false, 'message' => 'Only alliance leader can declare war'], 403);
        }

        if ($id == $validated['target_alliance_id']) {
            return response()->json(['success' => false, 'message' => 'Cannot declare war on yourself'], 400);
        }

        // Check if already at war
        $existingWar = AllianceWar::where(function ($q) use ($id, $validated) {
            $q->where('attacker_id', $id)->where('defender_id', $validated['target_alliance_id']);
        })->orWhere(function ($q) use ($id, $validated) {
            $q->where('attacker_id', $validated['target_alliance_id'])->where('defender_id', $id);
        })->active()->first();

        if ($existingWar) {
            return response()->json(['success' => false, 'message' => 'Already at war with this alliance'], 400);
        }

        // Check cost
        if ($alliance->treasury < 50000) {
            return response()->json(['success' => false, 'message' => 'Not enough alliance funds (50,000 required)'], 400);
        }

        $alliance->decrement('treasury', 50000);

        $war = AllianceWar::create([
            'attacker_id' => $id,
            'defender_id' => $validated['target_alliance_id'],
            'reason' => $validated['reason'] ?? 'No reason given',
            'status' => 'active',
            'attacker_score' => 0,
            'defender_score' => 0,
            'started_at' => now(),
            'ends_at' => now()->addDays(7),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'War declared!',
            'war' => $war,
        ]);
    }

    /**
     * Get active wars
     */
    public function wars()
    {
        $wars = AllianceWar::active()
            ->with(['attacker:id,name,tag', 'defender:id,name,tag'])
            ->orderBy('started_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'wars' => $wars,
        ]);
    }
}
