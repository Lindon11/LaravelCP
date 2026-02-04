<?php

namespace App\Plugins\Employment\Services;

use App\Plugins\Employment\Models\Company;
use App\Plugins\Employment\Models\EmploymentPosition;
use App\Plugins\Employment\Models\PlayerEmployment;
use App\Core\Models\User;
use App\Plugins\Employment\Models\WorkShift;
use Illuminate\Support\Facades\DB;

class EmploymentService
{
    public function getAvailablePositions(User $user)
    {
        return EmploymentPosition::with('company')
            ->where('is_active', true)
            ->where('current_employees', '<', DB::raw('max_employees'))
            ->where('required_level', '<=', $user->level)
            ->get()
            ->map(function ($position) use ($user) {
                $position->can_apply = $user->intelligence >= $position->required_intelligence 
                    && $user->endurance >= $position->required_endurance;
                return $position;
            });
    }

    public function applyForJob(User $user, int $positionId)
    {
        // Check if user already has a job
        if (PlayerEmployment::where('user_id', $user->id)->where('is_active', true)->exists()) {
            throw new \Exception('You already have a job. Quit your current job first.');
        }

        $position = EmploymentPosition::with('company')->findOrFail($positionId);

        // Verify requirements
        if ($user->level < $position->required_level) {
            throw new \Exception('You do not meet the level requirement.');
        }

        if ($user->intelligence < $position->required_intelligence) {
            throw new \Exception('You do not meet the intelligence requirement.');
        }

        if ($user->endurance < $position->required_endurance) {
            throw new \Exception('You do not meet the endurance requirement.');
        }

        if ($position->current_employees >= $position->max_employees) {
            throw new \Exception('This position is full.');
        }

        DB::beginTransaction();
        try {
            $employment = PlayerEmployment::create([
                'user_id' => $user->id,
                'position_id' => $position->id,
                'company_id' => $position->company_id,
                'salary' => $position->base_salary,
                'hired_at' => now(),
                'is_active' => true,
            ]);

            $position->increment('current_employees');
            $position->company->increment('employees_count');

            DB::commit();
            return $employment->load(['position', 'company']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function work(User $user)
    {
        $employment = PlayerEmployment::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['position', 'company'])
            ->first();

        if (!$employment) {
            throw new \Exception('You do not have a job.');
        }

        // Check cooldown (once per day)
        if ($employment->last_work_at && $employment->last_work_at->isToday()) {
            throw new \Exception('You have already worked today. Come back tomorrow.');
        }

        // Calculate earnings based on performance
        $performanceMultiplier = $employment->performance_rating / 100;
        $earnings = (int)($employment->salary * $performanceMultiplier);
        
        // Random performance change
        $performanceChange = rand(-5, 10);
        $newPerformance = max(0, min(100, $employment->performance_rating + $performanceChange));

        DB::beginTransaction();
        try {
            WorkShift::create([
                'user_id' => $user->id,
                'player_employment_id' => $employment->id,
                'earnings' => $earnings,
                'performance_score' => $newPerformance,
                'worked_at' => now(),
            ]);

            $employment->update([
                'last_work_at' => now(),
                'performance_rating' => $newPerformance,
                'total_days_worked' => $employment->total_days_worked + 1,
                'total_earned' => $employment->total_earned + $earnings,
            ]);

            $user->increment('cash', $earnings);
            $employment->company->increment('total_profit', $earnings);

            DB::commit();

            return [
                'earnings' => $earnings,
                'performance_rating' => $newPerformance,
                'performance_change' => $performanceChange,
                'total_earned' => $employment->total_earned,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function quitJob(User $user)
    {
        $employment = PlayerEmployment::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$employment) {
            throw new \Exception('You do not have a job.');
        }

        DB::beginTransaction();
        try {
            $employment->update(['is_active' => false]);
            $employment->position->decrement('current_employees');
            $employment->company->decrement('employees_count');

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getCurrentJob(User $user)
    {
        return PlayerEmployment::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['position', 'company', 'shifts' => function ($query) {
                $query->orderBy('worked_at', 'desc')->limit(10);
            }])
            ->first();
    }
}
