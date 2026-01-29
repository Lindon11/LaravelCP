<?php

namespace App\Filament\Resources\ChatMessageResource\Pages;

use App\Filament\Resources\ChatMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateChatMessage extends CreateRecord
{
    protected static string $resource = ChatMessageResource::class;
}
