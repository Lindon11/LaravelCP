<?php

namespace App\Filament\Resources\UserInventoryResource\Pages;

use App\Filament\Resources\UserInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserInventory extends EditRecord
{
    protected static string $resource = UserInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
