<?php

namespace App\Filament\Widgets;

use App\Models\PlayerBan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestBans extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->can('manage-players');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PlayerBan::query()
                    ->where('is_active', true)
                    ->latest('banned_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('player.username')
                    ->label('Player')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'warning' => 'temporary',
                        'danger' => 'permanent',
                    ]),
                Tables\Columns\TextColumn::make('bannedBy.name')
                    ->label('Banned By'),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(50),
                Tables\Columns\TextColumn::make('banned_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->placeholder('Never'),
            ])
            ->heading('Latest Active Bans');
    }
}
