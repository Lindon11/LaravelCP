<?php

namespace App\Filament\Resources\IpBanResource\Pages;

use App\Filament\Resources\IpBanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIpBan extends EditRecord
{
    protected static string $resource = IpBanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
