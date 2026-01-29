<?php

namespace App\Filament\Resources\UserTimerResource\Pages;

use App\Filament\Resources\UserTimerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserTimer extends CreateRecord
{
    protected static string $resource = UserTimerResource::class;
}
