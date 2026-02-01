<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gang;
use App\Services\GangService;
use Illuminate\Http\Request;

class GangsController extends Controller
{
    public function __construct(
        protected GangService $gangService
    ) {}

    /**
     * Get gangs data
     */
    public function index(Request $request)
    {
        $player = $request->user();

        $gangs = Gang::with(['leader', 'members'])
            ->orderBy('respect', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($gang) {
                return [
                    'id' => $gang->id,
                    'name' => $gang->name,
                    'tag' => $gang->tag,
                    'description' => $gang->description,
                    'leader' => $gang->leader?->username,
                    'members' => $gang->getMemberCount(),
                    'max_members' => $gang->max_members,
                    'respect' => $gang->respect,
                    'bank' => $gang->bank,
                    'level' => $gang->level,
                ];
            });

        $myGang = $player->gang_id ? Gang::with('members')->find($player->gang_id) : null;

        return response()->json([
            'player' => $player,
            'myGang' => $myGang,
            'gangs' => $gangs,
            'creationCost' => GangService::GANG_CREATION_COST,
        ]);
    }

    /**
     * Create a gang
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:gangs,name',
            'tag' => 'required|string|max:5|unique:gangs,tag',
        ]);

        $player = $request->user();
        $result = $this->gangService->createGang($player, $request->name, $request->tag);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'player' => $player->fresh(),
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Leave gang
     */
    public function leave(Request $request)
    {
        $player = $request->user();
        $result = $this->gangService->leaveGang($player);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'player' => $player->fresh(),
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Kick a member
     */
    public function kick(Request $request, $playerId)
    {
        $player = $request->user();
        $result = $this->gangService->kickMember($player, $playerId);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Deposit to gang bank
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $player = $request->user();
        $result = $this->gangService->depositToBank($player, $request->amount);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'player' => $player->fresh(),
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Withdraw from gang bank
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $player = $request->user();
        $result = $this->gangService->withdrawFromBank($player, $request->amount);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'player' => $player->fresh(),
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }
}
