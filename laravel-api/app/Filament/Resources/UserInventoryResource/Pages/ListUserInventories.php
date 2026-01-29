<?php

namespace App\Filament\Resources\UserInventoryResource\Pages;

use App\Filament\Resources\UserInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserInventories extends ListRecords
{
    protected static string $resource = UserInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
