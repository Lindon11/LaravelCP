<?php

namespace App\Filament\Resources\PlayerBanResource\Pages;

use App\Filament\Resources\PlayerBanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlayerBan extends EditRecord
{
    protected static string $resource = PlayerBanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
