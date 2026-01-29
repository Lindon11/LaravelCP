<?php

namespace App\Filament\Resources\UserTimerResource\Pages;

use App\Filament\Resources\UserTimerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserTimers extends ListRecords
{
    protected static string $resource = UserTimerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
