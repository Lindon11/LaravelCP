<?php

namespace App\Plugins\Leaderboards\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\User;
use Illuminate\Http\Request;

class LeaderboardsController extends Controller
{
    /**
     * Get leaderboards data
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'level');

        $leaderboards = [
            'level' => $this->getLevelLeaderboard(),
            'respect' => $this->getRespectLeaderboard(),
            'cash' => $this->getCashLeaderboard(),
            'networth' => $this->getNetworthLeaderboard(),
        ];

        return response()->json([
            'leaderboards' => $leaderboards,
            'currentType' => $type,
        ]);
    }

    private function getLevelLeaderboard()
    {
        return User::select('id', 'username', 'level', 'experience', 'rank')
            ->orderBy('level', 'desc')
            ->orderBy('experience', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'id' => $user->id,
                    'username' => $user->username,
                    'level' => $user->level,
                    'experience' => $user->experience,
                    'rank_title' => $user->rank,
                ];
            });
    }

    private function getRespectLeaderboard()
    {
        return User::select('id', 'username', 'respect', 'level', 'rank')
            ->orderBy('respect', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'id' => $user->id,
                    'username' => $user->username,
                    'respect' => number_format($user->respect ?? 0),
                    'level' => $user->level,
                    'rank_title' => $user->rank,
                ];
            });
    }

    private function getCashLeaderboard()
    {
        return User::select('id', 'username', 'cash', 'level', 'rank')
            ->orderBy('cash', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'id' => $user->id,
                    'username' => $user->username,
                    'cash' => number_format($user->cash ?? 0),
                    'level' => $user->level,
                    'rank_title' => $user->rank,
                ];
            });
    }

    private function getNetworthLeaderboard()
    {
        return User::select('id', 'username', 'cash', 'bank', 'level', 'rank')
            ->orderByRaw('(COALESCE(cash, 0) + COALESCE(bank, 0)) DESC')
            ->limit(50)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'id' => $user->id,
                    'username' => $user->username,
                    'networth' => number_format(($user->cash ?? 0) + ($user->bank ?? 0)),
                    'level' => $user->level,
                    'rank_title' => $user->rank,
                ];
            });
    }
}
