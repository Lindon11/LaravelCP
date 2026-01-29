<?php

namespace App\Filament\Resources\UserEquipmentResource\Pages;

use App\Filament\Resources\UserEquipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserEquipment extends EditRecord
{
    protected static string $resource = UserEquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
