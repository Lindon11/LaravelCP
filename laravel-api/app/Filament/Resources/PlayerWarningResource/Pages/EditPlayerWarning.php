<?php

namespace App\Filament\Resources\PlayerWarningResource\Pages;

use App\Filament\Resources\PlayerWarningResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlayerWarning extends EditRecord
{
    protected static string $resource = PlayerWarningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
