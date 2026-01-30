<?php

namespace App\Modules\Gang\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Gang;
use App\Models\User;
use App\Services\GangService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GangController extends Controller
{
    protected $gangService;

    public function __construct(GangService $gangService)
    {
        $this->gangService = $gangService;
    }

    /**
     * Display gangs page
     */
    public function index()
    {
        $player = auth()->user()->load(['player.gang'])->player;

        if (!$player) {
            return redirect()->route('dashboard')->with('error', 'Player profile not found.');
        }

        $gangs = Gang::with(['leader', 'members'])->orderBy('respect', 'desc')->limit(50)->get()->map(function ($gang) {
            return [
                'id' => $gang->id,
                'name' => $gang->name,
                'tag' => $gang->tag,
                'description' => $gang->description,
                'leader' => $gang->leader->username,
                'members' => $gang->getMemberCount(),
                'max_members' => $gang->max_members,
                'respect' => $gang->respect,
                'bank' => $gang->bank,
                'level' => $gang->level,
            ];
        });

        return Inertia::render('Modules/Gang/Index', [
            'player' => $player,
            'myGang' => $player->gang,
            'gangs' => $gangs,
            'creationCost' => GangService::GANG_CREATION_COST,
        ]);
    }

    /**
     * Create gang
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:gangs,name',
            'tag' => 'required|string|max:5|unique:gangs,tag',
        ]);

        $player = auth()->user();
        $result = $this->gangService->createGang($player, $request->name, $request->tag);

        if ($result['success']) {
            return redirect()->route('gangs.index')->with('success', $result['message']);
        }
        return redirect()->route('gangs.index')->with('error', $result['message']);
    }

    /**
     * Leave gang
     */
    public function leave()
    {
        $player = auth()->user();
        $result = $this->gangService->leaveGang($player);

        if ($result['success']) {
            return redirect()->route('gangs.index')->with('success', $result['message']);
        }
        return redirect()->route('gangs.index')->with('error', $result['message']);
    }

    /**
     * Kick member
     */
    public function kick(User $target)
    {
        $player = auth()->user();
        $result = $this->gangService->kickMember($player, $target);

        if ($result['success']) {
            return redirect()->route('gangs.index')->with('success', $result['message']);
        }
        return redirect()->route('gangs.index')->with('error', $result['message']);
    }

    /**
     * Deposit to gang bank
     */
    public function deposit(Request $request)
    {
        $request->validate(['amount' => 'required|integer|min:1']);

        $player = auth()->user();
        $result = $this->gangService->depositToGangBank($player, $request->amount);

        if ($result['success']) {
            return redirect()->route('gangs.index')->with('success', $result['message']);
        }
        return redirect()->route('gangs.index')->with('error', $result['message']);
    }

    /**
     * Withdraw from gang bank
     */
    public function withdraw(Request $request)
    {
        $request->validate(['amount' => 'required|integer|min:1']);

        $player = auth()->user();
        $result = $this->gangService->withdrawFromGangBank($player, $request->amount);

        if ($result['success']) {
            return redirect()->route('gangs.index')->with('success', $result['message']);
        }
        return redirect()->route('gangs.index')->with('error', $result['message']);
    }
}
