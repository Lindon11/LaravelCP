<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizedCrimeAttemptResource\Pages;
use App\Filament\Resources\OrganizedCrimeAttemptResource\RelationManagers;
use App\Models\OrganizedCrimeAttempt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrganizedCrimeAttemptResource extends Resource
{
    protected static ?string $model = OrganizedCrimeAttempt::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Activity Logs';

    protected static ?int $navigationSort = 6;

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
            'index' => Pages\ListOrganizedCrimeAttempts::route('/'),
            'create' => Pages\CreateOrganizedCrimeAttempt::route('/create'),
            'edit' => Pages\EditOrganizedCrimeAttempt::route('/{record}/edit'),
        ];
    }
}
