<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DirectMessageResource\Pages;
use App\Filament\Resources\DirectMessageResource\RelationManagers;
use App\Models\DirectMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DirectMessageResource extends Resource
{
    protected static ?string $model = DirectMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Community';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Direct Messages';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Message Details')
                    ->schema([
                        Forms\Components\Select::make('from_user_id')
                            ->relationship('sender', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('From'),
                        Forms\Components\Select::make('to_user_id')
                            ->relationship('recipient', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('To'),
                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_read')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('read_at')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sender.name')
                    ->searchable()
                    ->sortable()
                    ->label('From'),
                Tables\Columns\TextColumn::make('recipient.name')
                    ->searchable()
                    ->sortable()
                    ->label('To'),
                Tables\Columns\TextColumn::make('message')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_read')
                    ->boolean(),
                Tables\Columns\TextColumn::make('read_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sender')
                    ->relationship('sender', 'name')
                    ->label('From'),
                Tables\Filters\SelectFilter::make('recipient')
                    ->relationship('recipient', 'name')
                    ->label('To'),
                Tables\Filters\TernaryFilter::make('is_read'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListDirectMessages::route('/'),
            'create' => Pages\CreateDirectMessage::route('/create'),
            'edit' => Pages\EditDirectMessage::route('/{record}/edit'),
        ];
    }
}
