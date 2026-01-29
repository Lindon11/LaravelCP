<?php

namespace App\Filament\Resources\OrganizedCrimeResource\Pages;

use App\Filament\Resources\OrganizedCrimeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrganizedCrime extends EditRecord
{
    protected static string $resource = OrganizedCrimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
