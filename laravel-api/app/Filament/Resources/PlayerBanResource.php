<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlayerBanResource\Pages;
use App\Filament\Resources\PlayerBanResource\RelationManagers;
use App\Models\PlayerBan;
use App\Models\User;
use App\Services\ModerationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlayerBanResource extends Resource
{
    protected static ?string $model = PlayerBan::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';
    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 8;
    
    public static function canAccess(): bool
    {
        return auth()->user()->can('manage-players');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Player')
                    ->relationship('player', 'username')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn ($record) => $record !== null),
                Forms\Components\Select::make('type')
                    ->options([
                        'temporary' => 'Temporary',
                        'permanent' => 'Permanent',
                    ])
                    ->required()
                    ->reactive()
                    ->disabled(fn ($record) => $record !== null),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expires At')
                    ->visible(fn ($get) => $get('type') === 'temporary')
                    ->required(fn ($get) => $get('type') === 'temporary')
                    ->minDate(now())
                    ->disabled(fn ($record) => $record !== null),
                Forms\Components\Textarea::make('reason')
                    ->required()
                    ->rows(3)
                    ->disabled(fn ($record) => $record !== null),
                Forms\Components\Textarea::make('unban_reason')
                    ->label('Unban Reason')
                    ->rows(3)
                    ->visible(fn ($record) => $record !== null && $record->is_active),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('player.username')
                    ->searchable()
                    ->sortable()
                    ->label('Player'),
                Tables\Columns\TextColumn::make('bannedBy.name')
                    ->label('Banned By')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'warning' => 'temporary',
                        'danger' => 'permanent',
                    ]),
                Tables\Columns\TextColumn::make('banned_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('unbanned_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'temporary' => 'Temporary',
                        'permanent' => 'Permanent',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All bans')
                    ->trueLabel('Active bans only')
                    ->falseLabel('Inactive bans only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('unban')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PlayerBan $record) => $record->is_active)
                    ->form([
                        Forms\Components\Textarea::make('unban_reason')
                            ->label('Unban Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (PlayerBan $record, array $data) {
                        $service = app(ModerationService::class);
                        $service->unbanPlayer($record, auth()->user(), $data['unban_reason']);
                        
                        Notification::make()
                            ->success()
                            ->title('Player Unbanned')
                            ->body("Player {$record->user->username} has been unbanned.")
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('banned_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlayerBans::route('/'),
            'create' => Pages\CreatePlayerBan::route('/create'),
            'view' => Pages\ViewPlayerBan::route('/{record}'),
            'edit' => Pages\EditPlayerBan::route('/{record}/edit'),
        ];
    }
}
