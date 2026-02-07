<?php

namespace App\Plugins\Crimes\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Crimes\CrimesModule;
use App\Plugins\Crimes\Models\Crime;
use Illuminate\Http\Request;

class CrimeController extends Controller
{
    protected CrimesModule $module;

    public function __construct()
    {
        $this->module = new CrimesModule();
    }

    /**
     * Display crimes page
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $crimes = $this->module->getAvailableCrimes($user);
        $stats = $this->module->getStats($user);

        $timer = $user->getTimer('crime');
        $cooldown = $timer ? max(0, now()->diffInSeconds($timer->expires_at, false)) : 0;

        return response()->json([
            'player' => $user,
            'crimes' => $crimes,
            'stats' => $stats,
            'cooldown' => $cooldown,
        ]);
    }

    /**
     * Attempt a crime
     */
    public function attempt(Request $request, Crime $crime)
    {
        $user = $request->user();

        $result = $this->module->attemptCrime($user, $crime);

        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Get crime statistics
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $stats = $this->module->getStats($user);

        return response()->json($stats);
    }
}
