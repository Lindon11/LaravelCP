<?php

namespace App\Filament\Resources\RaceParticipantResource\Pages;

use App\Filament\Resources\RaceParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRaceParticipant extends EditRecord
{
    protected static string $resource = RaceParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
