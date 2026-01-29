<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserTimerResource\Pages;
use App\Filament\Resources\UserTimerResource\RelationManagers;
use App\Models\UserTimer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserTimerResource extends Resource
{
    protected static ?string $model = UserTimer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Player Management';

    protected static ?int $navigationSort = 4;
    
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
            'index' => Pages\ListUserTimers::route('/'),
            'create' => Pages\CreateUserTimer::route('/create'),
            'edit' => Pages\EditUserTimer::route('/{record}/edit'),
        ];
    }
}
