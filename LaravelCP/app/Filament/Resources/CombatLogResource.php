<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CombatLogResource\Pages;
use App\Filament\Resources\CombatLogResource\RelationManagers;
use App\Models\CombatLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CombatLogResource extends Resource
{
    protected static ?string $model = CombatLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Activity Logs';

    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListCombatLogs::route('/'),
            'create' => Pages\CreateCombatLog::route('/create'),
            'edit' => Pages\EditCombatLog::route('/{record}/edit'),
        ];
    }
}
