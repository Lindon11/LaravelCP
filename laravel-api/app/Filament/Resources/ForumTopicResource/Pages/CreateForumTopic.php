<?php

namespace App\Filament\Resources\ForumTopicResource\Pages;

use App\Filament\Resources\ForumTopicResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateForumTopic extends CreateRecord
{
    protected static string $resource = ForumTopicResource::class;
}
