<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlayerResource\Pages;
use App\Filament\Resources\PlayerResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlayerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Player Management';

    protected static ?int $navigationSort = 1;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('level')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('health')
                    ->required()
                    ->numeric()
                    ->default(100),
                Forms\Components\TextInput::make('max_health')
                    ->required()
                    ->numeric()
                    ->default(100),
                Forms\Components\TextInput::make('cash')
                    ->required()
                    ->numeric()
                    ->default(1000),
                Forms\Components\TextInput::make('bank')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('respect')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('bullets')
                    ->required()
                    ->numeric()
                    ->default(10),
                Forms\Components\TextInput::make('gang_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('rank')
                    ->required()
                    ->maxLength(255)
                    ->default('Thug'),
                Forms\Components\DateTimePicker::make('last_crime_at'),
                Forms\Components\DateTimePicker::make('last_gta_at'),
                Forms\Components\DateTimePicker::make('jail_until'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('level')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('health')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_health')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cash')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bank')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('respect')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bullets')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gang_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rank')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_crime_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_gta_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jail_until')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPlayers::route('/'),
            'create' => Pages\CreatePlayer::route('/create'),
            'edit' => Pages\EditPlayer::route('/{record}/edit'),
        ];
    }
}
