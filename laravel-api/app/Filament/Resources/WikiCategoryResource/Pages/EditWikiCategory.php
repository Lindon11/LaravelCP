<?php

namespace App\Filament\Resources\WikiCategoryResource\Pages;

use App\Filament\Resources\WikiCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWikiCategory extends EditRecord
{
    protected static string $resource = WikiCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
