<?php

namespace App\Filament\Resources\RaceParticipantResource\Pages;

use App\Filament\Resources\RaceParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRaceParticipants extends ListRecords
{
    protected static string $resource = RaceParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
