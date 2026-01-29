<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GangResource\Pages;
use App\Filament\Resources\GangResource\RelationManagers;
use App\Models\Gang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GangResource extends Resource
{
    protected static ?string $model = Gang::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Community';

    protected static ?int $navigationSort = 1;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('leader_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('bank')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('respect')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('tag')
                    ->maxLength(10)
                    ->default(null),
                Forms\Components\TextInput::make('max_members')
                    ->required()
                    ->numeric()
                    ->default(10),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('leader_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bank')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('respect')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tag')
                    ->searchable(),
                Tables\Columns\TextColumn::make('max_members')
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
            'index' => Pages\ListGangs::route('/'),
            'create' => Pages\CreateGang::route('/create'),
            'edit' => Pages\EditGang::route('/{record}/edit'),
        ];
    }
}
