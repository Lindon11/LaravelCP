<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IpBanResource\Pages;
use App\Filament\Resources\IpBanResource\RelationManagers;
use App\Models\IpBan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IpBanResource extends Resource
{
    protected static ?string $model = IpBan::class;

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 7;

    public static function canAccess(): bool
    {
        return auth()->user()->can('manage-players');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->required()
                    ->ipv4()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->disabled(fn ($record) => $record !== null),
                Forms\Components\Textarea::make('reason')
                    ->required()
                    ->rows(3)
                    ->disabled(fn ($record) => $record !== null),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expires At (Optional)')
                    ->minDate(now())
                    ->disabled(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ip_address')
                    ->searchable()
                    ->copyable()
                    ->label('IP Address'),
                Tables\Columns\TextColumn::make('bannedBy.name')
                    ->label('Banned By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('banned_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable()
                    ->label('Active'),
            ])
            ->filters([
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
                    ->visible(fn (IpBan $record) => $record->is_active)
                    ->requiresConfirmation()
                    ->action(function (IpBan $record) {
                        $record->update(['is_active' => false]);
                        
                        Notification::make()
                            ->success()
                            ->title('IP Unbanned')
                            ->body("IP {$record->ip_address} has been unbanned.")
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
            'index' => Pages\ListIpBans::route('/'),
            'create' => Pages\CreateIpBan::route('/create'),
            'view' => Pages\ViewIpBan::route('/{record}'),
            'edit' => Pages\EditIpBan::route('/{record}/edit'),
        ];
    }
}
