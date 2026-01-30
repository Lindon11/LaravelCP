<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DetectiveReportResource\Pages;
use App\Filament\Resources\DetectiveReportResource\RelationManagers;
use App\Models\DetectiveReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DetectiveReportResource extends Resource
{
    protected static ?string $model = DetectiveReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Activity Logs';

    protected static ?int $navigationSort = 3;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('player', 'id')
                    ->required(),
                Forms\Components\Select::make('target_id')
                    ->relationship('target', 'id')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Textarea::make('location_info')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('hired_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('complete_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('player.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('target.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('hired_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('complete_at')
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
            'index' => Pages\ListDetectiveReports::route('/'),
            'create' => Pages\CreateDetectiveReport::route('/create'),
            'edit' => Pages\EditDetectiveReport::route('/{record}/edit'),
        ];
    }
}
