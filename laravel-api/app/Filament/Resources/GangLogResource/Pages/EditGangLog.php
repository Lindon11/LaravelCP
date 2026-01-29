<?php

namespace App\Filament\Resources\GangLogResource\Pages;

use App\Filament\Resources\GangLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGangLog extends EditRecord
{
    protected static string $resource = GangLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
