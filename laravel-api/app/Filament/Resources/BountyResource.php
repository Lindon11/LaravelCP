<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BountyResource\Pages;
use App\Filament\Resources\BountyResource\RelationManagers;
use App\Models\Bounty;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BountyResource extends Resource
{
    protected static ?string $model = Bounty::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Community';

    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('target_id')
                    ->relationship('target', 'id')
                    ->required(),
                Forms\Components\TextInput::make('placed_by')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('claimed_by')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Textarea::make('reason')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('claimed_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('target.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('placed_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('claimed_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('claimed_at')
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
            'index' => Pages\ListBounties::route('/'),
            'create' => Pages\CreateBounty::route('/create'),
            'edit' => Pages\EditBounty::route('/{record}/edit'),
        ];
    }
}
