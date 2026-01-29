<?php

namespace App\Filament\Resources\GangLogResource\Pages;

use App\Filament\Resources\GangLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGangLogs extends ListRecords
{
    protected static string $resource = GangLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
