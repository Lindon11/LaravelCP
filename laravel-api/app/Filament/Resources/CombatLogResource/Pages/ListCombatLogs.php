<?php

namespace App\Filament\Resources\CombatLogResource\Pages;

use App\Filament\Resources\CombatLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCombatLogs extends ListRecords
{
    protected static string $resource = CombatLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
