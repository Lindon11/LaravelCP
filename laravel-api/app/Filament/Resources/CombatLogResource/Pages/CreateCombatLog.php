<?php

namespace App\Filament\Resources\CombatLogResource\Pages;

use App\Filament\Resources\CombatLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCombatLog extends CreateRecord
{
    protected static string $resource = CombatLogResource::class;
}
