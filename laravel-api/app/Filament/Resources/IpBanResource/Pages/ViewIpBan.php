<?php

namespace App\Filament\Resources\IpBanResource\Pages;

use App\Filament\Resources\IpBanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewIpBan extends ViewRecord
{
    protected static string $resource = IpBanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
