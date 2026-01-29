<?php

namespace App\Filament\Resources\OrganizedCrimeAttemptResource\Pages;

use App\Filament\Resources\OrganizedCrimeAttemptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrganizedCrimeAttempts extends ListRecords
{
    protected static string $resource = OrganizedCrimeAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
