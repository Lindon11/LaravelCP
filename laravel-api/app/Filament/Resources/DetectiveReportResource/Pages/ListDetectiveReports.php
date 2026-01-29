<?php

namespace App\Filament\Resources\DetectiveReportResource\Pages;

use App\Filament\Resources\DetectiveReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetectiveReports extends ListRecords
{
    protected static string $resource = DetectiveReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
