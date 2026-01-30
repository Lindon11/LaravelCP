<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RankResource\Pages;
use App\Filament\Resources\RankResource\RelationManagers;
use App\Models\Rank;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RankResource extends Resource
{
    protected static ?string $model = Rank::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Rank Name'),
                Forms\Components\TextInput::make('required_exp')
                    ->required()
                    ->numeric()
                    ->label('Experience Required')
                    ->helperText('Total experience needed to reach this rank')
                    ->default(0),
                Forms\Components\TextInput::make('max_health')
                    ->required()
                    ->numeric()
                    ->label('Max Health')
                    ->helperText('Maximum health for users at this rank')
                    ->default(100),
                Forms\Components\TextInput::make('cash_reward')
                    ->required()
                    ->numeric()
                    ->label('Cash Reward')
                    ->helperText('Cash awarded when reaching this rank')
                    ->default(0),
                Forms\Components\TextInput::make('bullet_reward')
                    ->required()
                    ->numeric()
                    ->label('Bullet Reward')
                    ->helperText('Bullets awarded when reaching this rank')
                    ->default(0),
                Forms\Components\TextInput::make('user_limit')
                    ->numeric()
                    ->label('User Limit')
                    ->helperText('Maximum users allowed at this rank (0 = no limit)')
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Rank'),
                Tables\Columns\TextColumn::make('required_exp')
                    ->numeric()
                    ->sortable()
                    ->label('Experience'),
                Tables\Columns\TextColumn::make('max_health')
                    ->numeric()
                    ->sortable()
                    ->label('Max HP'),
                Tables\Columns\TextColumn::make('cash_reward')
                    ->money('USD')
                    ->sortable()
                    ->label('Cash Reward'),
                Tables\Columns\TextColumn::make('bullet_reward')
                    ->numeric()
                    ->sortable()
                    ->label('Bullets'),
                Tables\Columns\TextColumn::make('user_limit')
                    ->numeric()
                    ->sortable()
                    ->label('Limit')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state : 'None'),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
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
            ->defaultSort('required_exp', 'asc')
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
            'index' => Pages\ListRanks::route('/'),
            'create' => Pages\CreateRank::route('/create'),
            'edit' => Pages\EditRank::route('/{record}/edit'),
        ];
    }
}
