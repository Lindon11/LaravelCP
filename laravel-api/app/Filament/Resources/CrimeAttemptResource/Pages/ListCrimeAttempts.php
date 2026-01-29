<?php

namespace App\Filament\Resources\CrimeAttemptResource\Pages;

use App\Filament\Resources\CrimeAttemptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrimeAttempts extends ListRecords
{
    protected static string $resource = CrimeAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
