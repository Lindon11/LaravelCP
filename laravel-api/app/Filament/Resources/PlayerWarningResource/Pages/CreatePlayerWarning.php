<?php

namespace App\Filament\Resources\PlayerWarningResource\Pages;

use App\Filament\Resources\PlayerWarningResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePlayerWarning extends CreateRecord
{
    protected static string $resource = PlayerWarningResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['issued_by'] = auth()->id();
        $data['acknowledged'] = false;

        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->success()
            ->title('Warning Issued')
            ->body("Warning issued to {$this->record->user->username}.")
            ->send();
    }
}
