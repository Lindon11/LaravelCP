<?php

namespace App\Filament\Resources\CombatLogResource\Pages;

use App\Filament\Resources\CombatLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCombatLog extends EditRecord
{
    protected static string $resource = CombatLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
