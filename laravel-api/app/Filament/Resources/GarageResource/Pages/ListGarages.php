<?php

namespace App\Filament\Resources\GarageResource\Pages;

use App\Filament\Resources\GarageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGarages extends ListRecords
{
    protected static string $resource = GarageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
