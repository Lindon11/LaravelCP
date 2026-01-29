<?php

namespace App\Filament\Resources\OrganizedCrimeAttemptResource\Pages;

use App\Filament\Resources\OrganizedCrimeAttemptResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrganizedCrimeAttempt extends EditRecord
{
    protected static string $resource = OrganizedCrimeAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
