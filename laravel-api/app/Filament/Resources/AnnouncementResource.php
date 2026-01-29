<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Filament\Resources\AnnouncementResource\RelationManagers;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Support';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()->can('manage-system');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->options([
                        'info' => 'Info',
                        'warning' => 'Warning',
                        'success' => 'Success',
                        'danger' => 'Danger',
                    ])
                    ->required()
                    ->default('info'),
                Forms\Components\Select::make('target')
                    ->options([
                        'all' => 'All Players',
                        'online' => 'Online Players Only',
                        'level_range' => 'Level Range',
                        'location' => 'Specific Location',
                    ])
                    ->required()
                    ->reactive()
                    ->default('all'),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('min_level')
                            ->numeric()
                            ->visible(fn ($get) => $get('target') === 'level_range')
                            ->label('Minimum Level'),
                        Forms\Components\TextInput::make('max_level')
                            ->numeric()
                            ->visible(fn ($get) => $get('target') === 'level_range')
                            ->label('Maximum Level'),
                    ]),
                Forms\Components\Select::make('location_id')
                    ->relationship('location', 'name')
                    ->visible(fn ($get) => $get('target') === 'location')
                    ->label('Location'),
                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Publish At')
                    ->default(now())
                    ->required(),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expires At')
                    ->minDate(now()),
                Forms\Components\Toggle::make('is_sticky')
                    ->label('Pin to Top')
                    ->default(false),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'info',
                        'warning' => 'warning',
                        'success' => 'success',
                        'danger' => 'danger',
                    ]),
                Tables\Columns\TextColumn::make('target')
                    ->badge(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),
                Tables\Columns\IconColumn::make('is_sticky')
                    ->boolean()
                    ->label('Pinned'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'info' => 'Info',
                        'warning' => 'Warning',
                        'success' => 'Success',
                        'danger' => 'Danger',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All announcements')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
                Tables\Filters\TernaryFilter::make('is_sticky')
                    ->label('Pinned')
                    ->placeholder('All announcements')
                    ->trueLabel('Pinned only')
                    ->falseLabel('Not pinned'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'view' => Pages\ViewAnnouncement::route('/{record}'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
