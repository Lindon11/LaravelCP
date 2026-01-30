<?php

namespace App\Modules\Detective;

use App\Modules\Module;
use App\Models\User;
use App\Models\DetectiveReport;

/**
 * Detective Module
 * 
 * Handles detective system - hire detectives to investigate other players
 * Provides intel on rival activities and locations
 */
class DetectiveModule extends Module
{
    protected string $name = 'Detective';
    
    public function construct(): void
    {
        $this->config = [
            'cost' => 50000,
            'investigation_time' => 600, // 10 minutes
            'cooldown' => 1800, // 30 minutes
        ];
    }
    
    /**
     * Hire a detective to investigate a target
     */
    public function hireDetective(User $user, User $target): array
    {
        // Validation
        if ($user->id === $target->id) {
            return $this->error('You cannot investigate yourself');
        }
        
        // Check cooldown
        if ($user->hasTimer('detective_hire')) {
            return $this->error('You must wait before hiring another detective');
        }
        
        // Check cost
        if ($user->cash < $this->config['cost']) {
            return $this->error("Not enough cash. Need {$this->money($this->config['cost'])}");
        }
        
        // Check for existing active report
        $existingReport = DetectiveReport::where('investigator_id', $user->id)
            ->where('target_id', $target->id)
            ->where('completed', false)
            ->first();
        
        if ($existingReport) {
            return $this->error('You already have an active investigation on this target');
        }
        
        // Apply hooks before hiring
        $this->applyModuleHook('beforeDetectiveHire', [
            'user' => $user,
            'target' => $target,
        ]);
        
        // Create investigation
        $report = DetectiveReport::create([
            'investigator_id' => $user->id,
            'target_id' => $target->id,
            'completed' => false,
            'complete_at' => now()->addSeconds($this->config['investigation_time']),
        ]);
        
        // Deduct cost
        $user->cash -= $this->config['cost'];
        $user->save();
        
        // Set cooldown
        $user->setTimer('detective_hire', $this->config['cooldown']);
        
        // Track action
        $this->trackAction($user, 'detective_hired', [
            'report_id' => $report->id,
            'target_id' => $target->id,
        ]);
        
        // Fire hook after hiring
        $this->applyModuleHook('afterDetectiveHire', [
            'user' => $user,
            'target' => $target,
            'report' => $report,
        ]);
        
        $minutes = floor($this->config['investigation_time'] / 60);
        return $this->success("Detective hired! Investigation will complete in {$minutes} minutes");
    }
    
    /**
     * Complete investigation and generate report
     */
    public function completeInvestigation(DetectiveReport $report): array
    {
        if ($report->completed) {
            return $this->error('Investigation already completed');
        }
        
        if (now()->lt($report->complete_at)) {
            return $this->error('Investigation not yet complete');
        }
        
        $target = User::find($report->target_id);
        
        // Generate report data
        $reportData = [
            'level' => $target->level,
            'location' => $target->location,
            'gang' => $target->gang ? $target->gang->name : 'None',
            'cash_on_hand' => $target->cash,
            'last_online' => $target->last_online,
            'strength' => $target->strength,
            'defense' => $target->defense,
            'speed' => $target->speed,
        ];
        
        // Apply hooks to modify report
        $reportData = $this->applyModuleHook('modifyDetectiveReport', [
            'report' => $report,
            'target' => $target,
            'data' => $reportData,
        ])['data'] ?? $reportData;
        
        // Save report
        $report->completed = true;
        $report->report_data = json_encode($reportData);
        $report->save();
        
        return $this->success('Investigation complete! Report available', $reportData);
    }
    
    /**
     * Get user's detective reports
     */
    public function getMyReports(User $user): array
    {
        return DetectiveReport::where('investigator_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'target' => User::find($report->target_id)->username,
                    'completed' => $report->completed,
                    'complete_at' => $report->complete_at,
                    'data' => $report->completed ? json_decode($report->report_data, true) : null,
                ];
            })
            ->toArray();
    }
}
