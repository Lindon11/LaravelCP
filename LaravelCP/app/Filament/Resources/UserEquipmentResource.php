<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserEquipmentResource\Pages;
use App\Filament\Resources\UserEquipmentResource\RelationManagers;
use App\Models\UserEquipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserEquipmentResource extends Resource
{
    protected static ?string $model = UserEquipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Player Management';

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
            'index' => Pages\ListUserEquipment::route('/'),
            'create' => Pages\CreateUserEquipment::route('/create'),
            'edit' => Pages\EditUserEquipment::route('/{record}/edit'),
        ];
    }
}
