<?php

namespace App\Filament\Resources\UserEquipmentResource\Pages;

use App\Filament\Resources\UserEquipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserEquipment extends ListRecords
{
    protected static string $resource = UserEquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
