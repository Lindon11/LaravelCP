<?php

namespace App\Filament\Resources\PlayerWarningResource\Pages;

use App\Filament\Resources\PlayerWarningResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlayerWarnings extends ListRecords
{
    protected static string $resource = PlayerWarningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
