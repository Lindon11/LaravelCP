<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MissionResource\Pages;
use App\Filament\Resources\MissionResource\RelationManagers;
use App\Models\Mission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MissionResource extends Resource
{
    protected static ?string $model = Mission::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Game Content';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('type')
                            ->options([
                                'daily' => 'Daily',
                                'story' => 'Story',
                                'repeatable' => 'Repeatable',
                                'one_time' => 'One Time',
                            ])
                            ->required()
                            ->default('repeatable'),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('required_level')
                                    ->label('Required Level')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1),
                                Forms\Components\Select::make('required_location_id')
                                    ->label('Required Location')
                                    ->relationship('location', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                            ]),
                    ]),

                Forms\Components\Section::make('Objectives')
                    ->schema([
                        Forms\Components\Select::make('objective_type')
                            ->label('Objective Type')
                            ->options([
                                'crime' => 'Complete Crimes',
                                'combat' => 'Win Combats',
                                'travel' => 'Visit Location',
                                'steal' => 'Steal Vehicles',
                                'gym' => 'Train at Gym',
                                'property' => 'Own Properties',
                                'gang' => 'Join a Gang',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('objective_count')
                            ->label('Required Count')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                        Forms\Components\KeyValue::make('objective_data')
                            ->label('Additional Data (JSON)')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->helperText('Optional extra parameters for the objective'),
                    ]),

                Forms\Components\Section::make('Rewards')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('cash_reward')
                                    ->label('Cash Reward')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('$'),
                                Forms\Components\TextInput::make('respect_reward')
                                    ->label('Respect Reward')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\TextInput::make('experience_reward')
                                    ->label('XP Reward')
                                    ->numeric()
                                    ->default(0),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('item_reward_id')
                                    ->label('Item Reward')
                                    ->relationship('itemReward', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                Forms\Components\TextInput::make('item_reward_quantity')
                                    ->label('Item Quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->visible(fn ($get) => $get('item_reward_id') !== null),
                            ]),
                    ]),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('cooldown_hours')
                                    ->label('Cooldown (Hours)')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('0 = no cooldown'),
                                Forms\Components\TextInput::make('order')
                                    ->label('Display Order')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Lower numbers appear first'),
                            ]),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'story',
                        'success' => 'daily',
                        'warning' => 'repeatable',
                        'info' => 'one_time',
                    ]),
                Tables\Columns\TextColumn::make('required_level')
                    ->label('Level')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->placeholder('Any')
                    ->sortable(),
                Tables\Columns\TextColumn::make('objective_type')
                    ->label('Objective')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('objective_count')
                    ->label('Count')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('cash_reward')
                    ->label('Cash')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('respect_reward')
                    ->label('Respect')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('experience_reward')
                    ->label('XP')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('itemReward.name')
                    ->label('Item Reward')
                    ->placeholder('None')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cooldown_hours')
                    ->label('Cooldown')
                    ->suffix('h')
                    ->placeholder('None')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'daily' => 'Daily',
                        'story' => 'Story',
                        'repeatable' => 'Repeatable',
                        'one_time' => 'One Time',
                    ]),
                Tables\Filters\SelectFilter::make('objective_type')
                    ->label('Objective')
                    ->options([
                        'crime' => 'Complete Crimes',
                        'combat' => 'Win Combats',
                        'travel' => 'Visit Location',
                        'steal' => 'Steal Vehicles',
                        'gym' => 'Train at Gym',
                        'property' => 'Own Properties',
                        'gang' => 'Join a Gang',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All missions')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Mission $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Mission $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Mission $record) => $record->is_active ? 'warning' : 'success')
                    ->action(fn (Mission $record) => $record->update(['is_active' => !$record->is_active]))
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc');
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
            'index' => Pages\ListMissions::route('/'),
            'create' => Pages\CreateMission::route('/create'),
            'view' => Pages\ViewMission::route('/{record}'),
            'edit' => Pages\EditMission::route('/{record}/edit'),
        ];
    }
}
