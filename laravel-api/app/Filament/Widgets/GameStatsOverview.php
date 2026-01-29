<?php

namespace App\Filament\Widgets;

use App\Models\Bounty;
use App\Models\DetectiveReport;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumTopic;
use App\Models\Gang;
use App\Models\Location;
use App\Models\OrganizedCrime;
use App\Models\OrganizedCrimeAttempt;
use App\Models\User;
use App\Models\Property;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GameStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Players', User::count())
                ->description('Registered players')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
                
            Stat::make('Active Gangs', Gang::count())
                ->description('Total gangs created')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('warning'),
                
            Stat::make('Active Bounties', Bounty::where('status', 'active')->count())
                ->description('Total: $' . number_format(Bounty::where('status', 'active')->sum('amount')))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger'),
                
            Stat::make('Properties Owned', Property::whereNotNull('owner_id')->count())
                ->description(Property::whereNull('owner_id')->count() . ' available')
                ->descriptionIcon('heroicon-m-home')
                ->color('info'),
                
            Stat::make('Detective Reports', DetectiveReport::where('status', 'investigating')->count())
                ->description('Active investigations')
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('gray'),
                
            Stat::make('Locations', Location::count())
                ->description('Travel destinations')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('primary'),
                
            Stat::make('Organized Crimes', OrganizedCrimeAttempt::whereDate('attempted_at', today())->count())
                ->description('Attempted today')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('danger'),
                
            Stat::make('Forum Topics', ForumTopic::count())
                ->description(ForumPost::count() . ' total posts')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('success'),
        ];
    }
}
