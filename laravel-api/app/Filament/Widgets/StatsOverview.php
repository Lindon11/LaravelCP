<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\PlayerBan;
use App\Models\PlayerWarning;
use App\Models\Gang;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()->can('manage-system');
    }

    protected function getStats(): array
    {
        $totalPlayers = User::count();
        $activePlayers = User::where('updated_at', '>=', now()->subDay())->count();
        $onlinePlayers = User::where('updated_at', '>=', now()->subMinutes(15))->count();
        $activeBans = PlayerBan::where('is_active', true)->count();
        $recentWarnings = PlayerWarning::where('created_at', '>=', now()->subDay())->count();
        $totalGangs = Gang::count();

        return [
            Stat::make('Total Players', $totalPlayers)
                ->description('Registered accounts')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Online Now', $onlinePlayers)
                ->description('Active in last 15 minutes')
                ->descriptionIcon('heroicon-o-signal')
                ->color('success'),
            Stat::make('Active Today', $activePlayers)
                ->description('Players online in last 24h')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info'),
            Stat::make('Active Bans', $activeBans)
                ->description($recentWarnings . ' warnings issued today')
                ->descriptionIcon('heroicon-o-shield-exclamation')
                ->color($activeBans > 0 ? 'danger' : 'success'),
            Stat::make('Total Gangs', $totalGangs)
                ->description('Active organizations')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('warning'),
        ];
    }
}
