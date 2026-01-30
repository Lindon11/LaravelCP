<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CrimeResource\Pages;
use App\Filament\Resources\CrimeResource\RelationManagers;
use App\Models\Crime;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CrimeResource extends Resource
{
    protected static ?string $model = Crime::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Game Content';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('success_rate')
                    ->required()
                    ->numeric()
                    ->default(50)
                    ->suffix('%'),
                Forms\Components\TextInput::make('min_cash')
                    ->required()
                    ->numeric()
                    ->default(100)
                    ->prefix('$'),
                Forms\Components\TextInput::make('max_cash')
                    ->required()
                    ->numeric()
                    ->default(500)
                    ->prefix('$'),
                Forms\Components\TextInput::make('respect_reward')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('experience_reward')
                    ->required()
                    ->numeric()
                    ->default(10)
                    ->suffix('XP'),
                Forms\Components\TextInput::make('energy_cost')
                    ->required()
                    ->numeric()
                    ->default(5),
                Forms\Components\TextInput::make('cooldown_seconds')
                    ->required()
                    ->numeric()
                    ->default(30)
                    ->suffix('seconds'),
                Forms\Components\TextInput::make('required_level')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\Select::make('difficulty')
                    ->required()
                    ->options([
                        'easy' => 'Easy',
                        'medium' => 'Medium',
                        'hard' => 'Hard',
                        'expert' => 'Expert',
                    ])
                    ->default('easy'),
                Forms\Components\Toggle::make('active')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('difficulty')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger',
                        'expert' => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('success_rate')
                    ->numeric()
                    ->sortable()
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('min_cash')
                    ->numeric()
                    ->sortable()
                    ->money('USD'),
                Tables\Columns\TextColumn::make('max_cash')
                    ->numeric()
                    ->sortable()
                    ->money('USD'),
                Tables\Columns\TextColumn::make('respect_reward')
                    ->numeric()
                    ->sortable()
                    ->label('Respect'),
                Tables\Columns\TextColumn::make('experience_reward')
                    ->numeric()
                    ->sortable()
                    ->label('XP')
                    ->suffix(' XP'),
                Tables\Columns\TextColumn::make('energy_cost')
                    ->numeric()
                    ->sortable()
                    ->label('Energy'),
                Tables\Columns\TextColumn::make('cooldown_seconds')
                    ->numeric()
                    ->sortable()
                    ->label('Cooldown')
                    ->suffix('s'),
                Tables\Columns\TextColumn::make('required_level')
                    ->numeric()
                    ->sortable()
                    ->label('Level'),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
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
            'index' => Pages\ListCrimes::route('/'),
            'create' => Pages\CreateCrime::route('/create'),
            'edit' => Pages\EditCrime::route('/{record}/edit'),
        ];
    }
}
