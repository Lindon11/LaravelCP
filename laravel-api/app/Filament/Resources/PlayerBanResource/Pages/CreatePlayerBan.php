<?php

namespace App\Filament\Resources\PlayerBanResource\Pages;

use App\Filament\Resources\PlayerBanResource;
use App\Services\ModerationService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePlayerBan extends CreateRecord
{
    protected static string $resource = PlayerBanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['banned_by'] = auth()->id();
        $data['banned_at'] = now();
        $data['is_active'] = true;

        return $data;
    }

    protected function afterCreate(): void
    {
        $service = app(ModerationService::class);
        
        // Use the service to handle the ban logic (update player.is_banned, etc.)
        $player = $this->record->player;
        $player->update(['is_banned' => true]);

        Notification::make()
            ->success()
            ->title('Player Banned')
            ->body("Player {$player->username} has been banned successfully.")
            ->send();
    }
}
