<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatChannelResource\Pages;
use App\Filament\Resources\ChatChannelResource\RelationManagers;
use App\Models\ChatChannel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChatChannelResource extends Resource
{
    protected static ?string $model = ChatChannel::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Community';

    protected static ?int $navigationSort = 7;
    protected static ?string $navigationLabel = 'Chat Channels';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Channel Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'public' => 'Public',
                                'private' => 'Private',
                                'group' => 'Group',
                                'announcement' => 'Announcement',
                            ])
                            ->required(),
                        Forms\Components\Select::make('created_by')
                            ->relationship('creator', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('max_members')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'public' => 'blue',
                        'private' => 'red',
                        'group' => 'yellow',
                        'announcement' => 'green',
                    }),
                Tables\Columns\TextColumn::make('creator.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('messages_count')
                    ->counts('messages')
                    ->label('Messages'),
                Tables\Columns\TextColumn::make('members_count')
                    ->counts('members')
                    ->label('Members'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'public' => 'Public',
                        'private' => 'Private',
                        'group' => 'Group',
                        'announcement' => 'Announcement',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
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
            'index' => Pages\ListChatChannels::route('/'),
            'create' => Pages\CreateChatChannel::route('/create'),
            'edit' => Pages\EditChatChannel::route('/{record}/edit'),
        ];
    }
}
