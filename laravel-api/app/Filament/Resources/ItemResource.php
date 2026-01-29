<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Game Content';

    protected static ?int $navigationSort = 3;
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'weapon' => 'Weapon',
                                'armor' => 'Armor',
                                'consumable' => 'Consumable',
                                'vehicle' => 'Vehicle',
                                'misc' => 'Miscellaneous',
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('items'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                        Forms\Components\TextInput::make('sell_price')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Properties')
                    ->schema([
                        Forms\Components\Toggle::make('tradeable')
                            ->default(true),
                        Forms\Components\Toggle::make('stackable')
                            ->default(false)
                            ->reactive(),
                        Forms\Components\TextInput::make('max_stack')
                            ->numeric()
                            ->default(1)
                            ->visible(fn ($get) => $get('stackable')),
                        Forms\Components\Select::make('rarity')
                            ->required()
                            ->options([
                                'common' => 'Common',
                                'uncommon' => 'Uncommon',
                                'rare' => 'Rare',
                                'epic' => 'Epic',
                                'legendary' => 'Legendary',
                            ])
                            ->default('common'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Stats & Requirements')
                    ->schema([
                        Forms\Components\KeyValue::make('stats')
                            ->label('Stats (e.g., damage, defense, speed, health)')
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('requirements')
                            ->label('Requirements (e.g., level)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'weapon' => 'danger',
                        'armor' => 'success',
                        'consumable' => 'info',
                        'vehicle' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('rarity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'legendary' => 'warning',
                        'epic' => 'danger',
                        'rare' => 'info',
                        'uncommon' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\IconColumn::make('tradeable')
                    ->boolean(),
                Tables\Columns\IconColumn::make('stackable')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'weapon' => 'Weapon',
                        'armor' => 'Armor',
                        'consumable' => 'Consumable',
                        'vehicle' => 'Vehicle',
                        'misc' => 'Miscellaneous',
                    ]),
                Tables\Filters\SelectFilter::make('rarity')
                    ->options([
                        'common' => 'Common',
                        'uncommon' => 'Uncommon',
                        'rare' => 'Rare',
                        'epic' => 'Epic',
                        'legendary' => 'Legendary',
                    ]),
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
