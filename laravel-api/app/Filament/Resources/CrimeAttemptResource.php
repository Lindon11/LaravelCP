<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CrimeAttemptResource\Pages;
use App\Filament\Resources\CrimeAttemptResource\RelationManagers;
use App\Models\CrimeAttempt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CrimeAttemptResource extends Resource
{
    protected static ?string $model = CrimeAttempt::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Activity Logs';

    protected static ?int $navigationSort = 1;
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
            'index' => Pages\ListCrimeAttempts::route('/'),
            'create' => Pages\CreateCrimeAttempt::route('/create'),
            'edit' => Pages\EditCrimeAttempt::route('/{record}/edit'),
        ];
    }
}
