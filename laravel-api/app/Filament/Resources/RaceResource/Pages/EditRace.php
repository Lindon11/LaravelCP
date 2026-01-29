<?php

namespace App\Filament\Resources\RaceResource\Pages;

use App\Filament\Resources\RaceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRace extends EditRecord
{
    protected static string $resource = RaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
