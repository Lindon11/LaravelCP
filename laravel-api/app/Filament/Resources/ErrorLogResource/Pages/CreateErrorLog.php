<?php

namespace App\Filament\Resources\ErrorLogResource\Pages;

use App\Filament\Resources\ErrorLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateErrorLog extends CreateRecord
{
    protected static string $resource = ErrorLogResource::class;
}
