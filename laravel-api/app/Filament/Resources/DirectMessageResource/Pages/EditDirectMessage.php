<?php

namespace App\Filament\Resources\DirectMessageResource\Pages;

use App\Filament\Resources\DirectMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDirectMessage extends EditRecord
{
    protected static string $resource = DirectMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
