<?php

namespace App\Plugins\Employment;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Plugins\Employment\Models\Company;
use App\Plugins\Employment\Models\EmploymentPosition;
use App\Plugins\Employment\Models\UserEmployment;

/**
 * Employment Module
 * 
 * Handles employment system allowing players to work at companies
 */
class EmploymentModule extends Plugin
{
    protected string $name = 'Employment';
    
    public function construct(): void
    {
        $this->config = [
            'work_cooldown' => 900, // 15 minutes
            'energy_cost' => 5,
        ];
    }
    
    /**
     * Get available companies
     */
    public function getAvailableCompanies(): array
    {
        return Company::with(['positions' => function ($query) {
            $query->where('is_active', true)
                  ->orderBy('level_requirement');
        }])
        ->where('is_active', true)
        ->get()
        ->toArray();
    }
    
    /**
     * Get user's current employment
     */
    public function getCurrentEmployment(User $user): ?UserEmployment
    {
        return UserEmployment::with(['company', 'position'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }
    
    /**
     * Apply for a job
     */
    public function applyForJob(User $user, EmploymentPosition $position): array
    {
        // Check if already employed
        $currentJob = $this->getCurrentEmployment($user);
        if ($currentJob) {
            return [
                'success' => false,
                'message' => 'You must quit your current job first!'
            ];
        }
        
        // Check level requirement
        if ($user->level < $position->level_requirement) {
            return [
                'success' => false,
                'message' => "You need to be level {$position->level_requirement} to apply for this position."
            ];
        }
        
        // Check stats requirements
        if ($position->strength_requirement && $user->strength < $position->strength_requirement) {
            return [
                'success' => false,
                'message' => "You need {$position->strength_requirement} strength for this position."
            ];
        }
        
        if ($position->intelligence_requirement && $user->intelligence < $position->intelligence_requirement) {
            return [
                'success' => false,
                'message' => "You need {$position->intelligence_requirement} intelligence for this position."
            ];
        }
        
        // Create employment record
        UserEmployment::create([
            'user_id' => $user->id,
            'company_id' => $position->company_id,
            'position_id' => $position->id,
            'salary' => $position->base_salary,
            'is_active' => true,
        ]);
        
        return [
            'success' => true,
            'message' => "Congratulations! You've been hired as a {$position->name}!"
        ];
    }
    
    /**
     * Work at current job
     */
    public function work(User $user): array
    {
        $employment = $this->getCurrentEmployment($user);
        
        if (!$employment) {
            return [
                'success' => false,
                'message' => 'You are not employed!'
            ];
        }
        
        // Check cooldown
        if ($employment->last_work_at && now()->diffInSeconds($employment->last_work_at) < $this->config['work_cooldown']) {
            $remaining = $this->config['work_cooldown'] - now()->diffInSeconds($employment->last_work_at);
            return [
                'success' => false,
                'message' => "You can work again in " . gmdate("i:s", $remaining)
            ];
        }
        
        // Check energy
        if ($user->energy < $this->config['energy_cost']) {
            return [
                'success' => false,
                'message' => "You need at least {$this->config['energy_cost']} energy to work!"
            ];
        }
        
        // Calculate earnings with performance bonus
        $performance = rand(80, 120) / 100;
        $earnings = round($employment->salary * $performance);
        $exp = rand(5, 15);
        
        // Update user
        $user->cash += $earnings;
        $user->energy -= $this->config['energy_cost'];
        $user->experience += $exp;
        $user->save();
        
        // Update employment
        $employment->last_work_at = now();
        $employment->total_shifts++;
        $employment->save();
        
        return [
            'success' => true,
            'message' => "You worked and earned $" . number_format($earnings) . " and {$exp} XP!",
            'earnings' => $earnings,
            'exp' => $exp,
        ];
    }
    
    /**
     * Quit current job
     */
    public function quitJob(User $user): array
    {
        $employment = $this->getCurrentEmployment($user);
        
        if (!$employment) {
            return [
                'success' => false,
                'message' => 'You are not employed!'
            ];
        }
        
        $employment->is_active = false;
        $employment->quit_at = now();
        $employment->save();
        
        return [
            'success' => true,
            'message' => 'You have quit your job.'
        ];
    }
    
    /**
     * Get employment stats
     */
    public function getStats(User $user): array
    {
        $employment = $this->getCurrentEmployment($user);
        
        $totalEarned = UserEmployment::where('user_id', $user->id)
            ->sum('total_shifts');
        
        return [
            'currently_employed' => $employment ? true : false,
            'current_position' => $employment?->position->name ?? 'Unemployed',
            'current_company' => $employment?->company->name ?? null,
            'current_salary' => $employment?->salary ?? 0,
            'total_shifts_worked' => $totalEarned,
            'can_work' => $employment && (!$employment->last_work_at || now()->diffInSeconds($employment->last_work_at) >= $this->config['work_cooldown']),
        ];
    }
}
