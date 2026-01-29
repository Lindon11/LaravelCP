<?php

namespace App\Filament\Resources\TheftTypeResource\Pages;

use App\Filament\Resources\TheftTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTheftType extends EditRecord
{
    protected static string $resource = TheftTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
