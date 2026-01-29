<?php

namespace App\Filament\Resources\CrimeResource\Pages;

use App\Filament\Resources\CrimeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCrime extends EditRecord
{
    protected static string $resource = CrimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
