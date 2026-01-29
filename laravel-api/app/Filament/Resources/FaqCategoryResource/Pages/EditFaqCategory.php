<?php

namespace App\Filament\Resources\FaqCategoryResource\Pages;

use App\Filament\Resources\FaqCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFaqCategory extends EditRecord
{
    protected static string $resource = FaqCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
