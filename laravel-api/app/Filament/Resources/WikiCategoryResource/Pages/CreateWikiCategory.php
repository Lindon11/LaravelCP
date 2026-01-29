<?php

namespace App\Filament\Resources\WikiCategoryResource\Pages;

use App\Filament\Resources\WikiCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWikiCategory extends CreateRecord
{
    protected static string $resource = WikiCategoryResource::class;
}
