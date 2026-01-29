<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlayerWarningResource\Pages;
use App\Filament\Resources\PlayerWarningResource\RelationManagers;
use App\Models\PlayerWarning;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlayerWarningResource extends Resource
{
    protected static ?string $model = PlayerWarning::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 9;
    
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
                Forms\Components\Select::make('severity')
                    ->options([
                        'minor' => 'Minor',
                        'moderate' => 'Moderate',
                        'severe' => 'Severe',
                    ])
                    ->required()
                    ->disabled(fn ($record) => $record !== null),
                Forms\Components\Textarea::make('reason')
                    ->required()
                    ->rows(3)
                    ->disabled(fn ($record) => $record !== null),
                Forms\Components\Toggle::make('acknowledged')
                    ->label('Acknowledged by Player')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('acknowledged_at')
                    ->label('Acknowledged At')
                    ->disabled(),
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
                Tables\Columns\TextColumn::make('issuedBy.name')
                    ->label('Issued By')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('severity')
                    ->colors([
                        'success' => 'minor',
                        'warning' => 'moderate',
                        'danger' => 'severe',
                    ]),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('acknowledged')
                    ->boolean()
                    ->label('Acknowledged'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Issued At'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'minor' => 'Minor',
                        'moderate' => 'Moderate',
                        'severe' => 'Severe',
                    ]),
                Tables\Filters\TernaryFilter::make('acknowledged')
                    ->label('Acknowledged')
                    ->placeholder('All warnings')
                    ->trueLabel('Acknowledged')
                    ->falseLabel('Unacknowledged'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPlayerWarnings::route('/'),
            'create' => Pages\CreatePlayerWarning::route('/create'),
            'view' => Pages\ViewPlayerWarning::route('/{record}'),
            'edit' => Pages\EditPlayerWarning::route('/{record}/edit'),
        ];
    }
}
