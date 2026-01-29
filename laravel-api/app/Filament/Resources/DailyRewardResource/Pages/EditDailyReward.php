<?php

namespace App\Filament\Resources\DailyRewardResource\Pages;

use App\Filament\Resources\DailyRewardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDailyReward extends EditRecord
{
    protected static string $resource = DailyRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
