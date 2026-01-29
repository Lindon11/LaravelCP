<?php

namespace App\Services;

use App\Models\DetectiveReport;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DetectiveService
{
    const DETECTIVE_COST = 5000;
    const INVESTIGATION_TIME = 300; // 5 minutes in seconds

    public function hireDetective(User $player, User $target): array
    {
        if ($player->id === $target->id) {
            throw new \Exception('You cannot hire a detective to find yourself.');
        }

        // Check for existing active investigation
        $existing = DetectiveReport::where('user_id', $player->id)
            ->where('target_id', $target->id)
            ->where('status', 'investigating')
            ->first();

        if ($existing) {
            $timeRemaining = $this->getTimeRemaining($existing);
            throw new \Exception('You already have a detective investigating ' . $target->username . '. Report ready in ' . gmdate('i:s', $timeRemaining));
        }

        if ($player->cash < self::DETECTIVE_COST) {
            throw new \Exception('You need $' . number_format(self::DETECTIVE_COST) . ' to hire a detective.');
        }

        return DB::transaction(function () use ($player, $target) {
            $player->decrement('cash', self::DETECTIVE_COST);

            $report = DetectiveReport::create([
                'user_id' => $player->id,
                'target_id' => $target->id,
                'status' => 'investigating',
                'hired_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Detective hired to find ' . $target->username . '. Report will be ready in ' . (self::INVESTIGATION_TIME / 60) . ' minutes.',
                'report' => $report,
            ];
        });
    }

    public function checkReport(DetectiveReport $report): void
    {
        if ($report->isComplete()) {
            return;
        }

        $elapsed = now()->diffInSeconds($report->hired_at);

        if ($elapsed >= self::INVESTIGATION_TIME) {
            $report->update([
                'status' => 'complete',
                'complete_at' => now(),
                'location_info' => $this->generateLocationInfo($report->target),
            ]);
        }
    }

    public function getTimeRemaining(DetectiveReport $report): int
    {
        if ($report->isComplete()) {
            return 0;
        }

        $elapsed = now()->diffInSeconds($report->hired_at);
        $remaining = self::INVESTIGATION_TIME - $elapsed;

        return max(0, $remaining);
    }

    protected function generateLocationInfo(User $target): string
    {
        $activities = [
            'was spotted at the casino',
            'was seen leaving a nightclub',
            'was spotted at the docks',
            'was seen entering a warehouse',
            'was spotted at the gym',
            'was seen at a restaurant downtown',
            'was spotted near the bank',
            'was seen at the bar on 5th street',
        ];

        return $target->username . ' ' . $activities[array_rand($activities)] . ' recently.';
    }

    public function getMyReports(User $player)
    {
        return DetectiveReport::where('user_id', $player->id)
            ->with('target')
            ->orderByDesc('hired_at')
            ->get()
            ->map(function ($report) {
                $this->checkReport($report);
                $report->refresh();

                return [
                    'id' => $report->id,
                    'target' => $report->target->username,
                    'target_id' => $report->target_id,
                    'status' => $report->status,
                    'location_info' => $report->location_info,
                    'time_remaining' => $this->getTimeRemaining($report),
                    'hired_at' => $report->hired_at->diffForHumans(),
                ];
            });
    }
}
