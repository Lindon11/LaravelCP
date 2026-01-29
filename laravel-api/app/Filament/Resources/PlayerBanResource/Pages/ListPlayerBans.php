<?php

namespace App\Filament\Resources\PlayerBanResource\Pages;

use App\Filament\Resources\PlayerBanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlayerBans extends ListRecords
{
    protected static string $resource = PlayerBanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
