<?php

namespace App\Filament\Resources\GarageResource\Pages;

use App\Filament\Resources\GarageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGarage extends EditRecord
{
    protected static string $resource = GarageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
