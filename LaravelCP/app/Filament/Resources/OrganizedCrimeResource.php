<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizedCrimeResource\Pages;
use App\Filament\Resources\OrganizedCrimeResource\RelationManagers;
use App\Models\OrganizedCrime;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrganizedCrimeResource extends Resource
{
    protected static ?string $model = OrganizedCrime::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Game Content';

    protected static ?int $navigationSort = 8;
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
                    ->default(50),
                Forms\Components\TextInput::make('min_reward')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('max_reward')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('required_members')
                    ->required()
                    ->numeric()
                    ->default(3),
                Forms\Components\TextInput::make('required_level')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('cooldown')
                    ->required()
                    ->numeric()
                    ->default(3600),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('success_rate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_reward')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_reward')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('required_members')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('required_level')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cooldown')
                    ->numeric()
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
            'index' => Pages\ListOrganizedCrimes::route('/'),
            'create' => Pages\CreateOrganizedCrime::route('/create'),
            'edit' => Pages\EditOrganizedCrime::route('/{record}/edit'),
        ];
    }
}
