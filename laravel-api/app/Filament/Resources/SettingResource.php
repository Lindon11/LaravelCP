<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 4;    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Setting Details')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Unique identifier for this setting (e.g., game_name, max_energy)'),
                        
                        Forms\Components\Select::make('group')
                            ->required()
                            ->options([
                                'general' => 'General',
                                'game' => 'Gameplay',
                                'economy' => 'Economy',
                                'combat' => 'Combat',
                                'gangs' => 'Gangs',
                                'email' => 'Email',
                                'payment' => 'Payment',
                            ])
                            ->default('general'),
                        
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'string' => 'Text',
                                'integer' => 'Number (Integer)',
                                'float' => 'Number (Decimal)',
                                'boolean' => 'True/False',
                                'array' => 'Array/List',
                                'json' => 'JSON Object',
                            ])
                            ->default('string')
                            ->reactive(),
                        
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->helperText('Brief description of what this setting controls'),
                        
                        Forms\Components\Textarea::make('value')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('For arrays/JSON, enter valid JSON format'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('group')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'general' => 'gray',
                        'game' => 'success',
                        'economy' => 'warning',
                        'combat' => 'danger',
                        'gangs' => 'info',
                        'email' => 'primary',
                        'payment' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('value')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('description')
                    ->limit(60)
                    ->toggleable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'general' => 'General',
                        'game' => 'Gameplay',
                        'economy' => 'Economy',
                        'combat' => 'Combat',
                        'gangs' => 'Gangs',
                        'email' => 'Email',
                        'payment' => 'Payment',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'string' => 'Text',
                        'integer' => 'Number (Integer)',
                        'float' => 'Number (Decimal)',
                        'boolean' => 'True/False',
                        'array' => 'Array/List',
                        'json' => 'JSON Object',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group');
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
