<?php

namespace App\Filament\Resources\DirectMessageResource\Pages;

use App\Filament\Resources\DirectMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDirectMessages extends ListRecords
{
    protected static string $resource = DirectMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
