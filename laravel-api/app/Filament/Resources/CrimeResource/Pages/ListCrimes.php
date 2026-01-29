<?php

namespace App\Filament\Resources\CrimeResource\Pages;

use App\Filament\Resources\CrimeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrimes extends ListRecords
{
    protected static string $resource = CrimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
