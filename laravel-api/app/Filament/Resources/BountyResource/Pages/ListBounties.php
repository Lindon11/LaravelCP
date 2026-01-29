<?php

namespace App\Filament\Resources\BountyResource\Pages;

use App\Filament\Resources\BountyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBounties extends ListRecords
{
    protected static string $resource = BountyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
