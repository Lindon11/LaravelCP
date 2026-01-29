<?php

namespace App\Filament\Resources\WikiPageResource\Pages;

use App\Filament\Resources\WikiPageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWikiPage extends CreateRecord
{
    protected static string $resource = WikiPageResource::class;
}
