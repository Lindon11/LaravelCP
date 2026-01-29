<?php

namespace App\Filament\Resources\ForumTopicResource\Pages;

use App\Filament\Resources\ForumTopicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListForumTopics extends ListRecords
{
    protected static string $resource = ForumTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
