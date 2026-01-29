<?php

namespace App\Filament\Resources\IpBanResource\Pages;

use App\Filament\Resources\IpBanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateIpBan extends CreateRecord
{
    protected static string $resource = IpBanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['banned_by'] = auth()->id();
        $data['banned_at'] = now();
        $data['is_active'] = true;

        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->success()
            ->title('IP Address Banned')
            ->body("IP {$this->record->ip_address} has been banned.")
            ->send();
    }
}
