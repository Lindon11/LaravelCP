<?php

namespace App\Filament\Resources\ChatChannelResource\Pages;

use App\Filament\Resources\ChatChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChatChannel extends EditRecord
{
    protected static string $resource = ChatChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
