<?php

namespace App\Filament\Resources\OrganizedCrimeResource\Pages;

use App\Filament\Resources\OrganizedCrimeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrganizedCrimes extends ListRecords
{
    protected static string $resource = OrganizedCrimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
