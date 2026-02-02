<?php

namespace App\Modules\Employment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Employment\EmploymentModule;
use App\Models\EmploymentPosition;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmploymentController extends Controller
{
    protected EmploymentModule $module;
    
    public function __construct()
    {
        $this->module = new EmploymentModule();
    }
    
    /**
     * Display employment page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $companies = $this->module->getAvailableCompanies();
        $stats = $this->module->getStats($user);
        $currentEmployment = $this->module->getCurrentEmployment($user);
        
        $timer = $user->getTimer('work');
        $cooldown = $timer ? max(0, now()->diffInSeconds($timer->expires_at, false)) : 0;
        
        return Inertia::render('Modules/Employment/Index', [
            'player' => $user,
            'companies' => $companies,
            'stats' => $stats,
            'currentEmployment' => $currentEmployment,
            'cooldown' => $cooldown,
        ]);
    }
    
    /**
     * Apply for a job
     */
    public function apply(Request $request, EmploymentPosition $position)
    {
        $user = $request->user();
        
        $result = $this->module->applyForJob($user, $position);
        
        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }
    
    /**
     * Work at current job
     */
    public function work(Request $request)
    {
        $user = $request->user();
        
        $result = $this->module->work($user);
        
        if ($result['success'] ?? false) {
            $user->setTimer('work', 900); // 15 minutes
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }
    
    /**
     * Quit current job
     */
    public function quit(Request $request)
    {
        $user = $request->user();
        
        $result = $this->module->quitJob($user);
        
        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }
}
