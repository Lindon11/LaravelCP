<?php

namespace App\Filament\Resources\DailyRewardResource\Pages;

use App\Filament\Resources\DailyRewardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDailyRewards extends ListRecords
{
    protected static string $resource = DailyRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
