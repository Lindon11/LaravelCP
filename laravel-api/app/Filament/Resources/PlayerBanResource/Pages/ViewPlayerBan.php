<?php

namespace App\Filament\Resources\PlayerBanResource\Pages;

use App\Filament\Resources\PlayerBanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPlayerBan extends ViewRecord
{
    protected static string $resource = PlayerBanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
