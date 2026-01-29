<?php

namespace App\Filament\Resources\DetectiveReportResource\Pages;

use App\Filament\Resources\DetectiveReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDetectiveReport extends EditRecord
{
    protected static string $resource = DetectiveReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
