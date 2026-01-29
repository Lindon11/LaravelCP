<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class PlayerActivityChart extends ChartWidget
{
    protected static ?string $heading = 'Player Activity (Last 7 Days)';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()->can('manage-system');
    }

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(function ($daysAgo) {
            return now()->subDays($daysAgo)->format('Y-m-d');
        });

        $activityData = $days->map(function ($date) {
            return User::whereDate('updated_at', $date)->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Active Players',
                    'data' => $activityData->values()->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
            ],
            'labels' => $days->map(fn ($date) => date('M j', strtotime($date)))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
