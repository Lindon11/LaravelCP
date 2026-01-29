<?php

namespace App\Filament\Resources\ChatChannelResource\Pages;

use App\Filament\Resources\ChatChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChatChannels extends ListRecords
{
    protected static string $resource = ChatChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
