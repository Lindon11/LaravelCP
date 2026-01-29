<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ErrorLogResource\Pages;
use App\Models\ErrorLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ErrorLogResource extends Resource
{
    protected static ?string $model = ErrorLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 6;
    
    protected static ?string $navigationLabel = 'Error Logs';
    
    public static function canCreate(): bool
    {
        return false; // Errors are automatically logged, not manually created
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Error Details')
                    ->schema([
                        Forms\Components\TextInput::make('type')
                            ->label('Exception Type')
                            ->disabled(),
                        Forms\Components\Textarea::make('message')
                            ->label('Error Message')
                            ->disabled()
                            ->rows(3),
                        Forms\Components\TextInput::make('file')
                            ->label('File')
                            ->disabled(),
                        Forms\Components\TextInput::make('line')
                            ->label('Line')
                            ->numeric()
                            ->disabled(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Request Information')
                    ->schema([
                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->disabled(),
                        Forms\Components\TextInput::make('method')
                            ->label('HTTP Method')
                            ->disabled(),
                        Forms\Components\TextInput::make('ip')
                            ->label('IP Address')
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Stack Trace')
                    ->schema([
                        Forms\Components\Textarea::make('trace')
                            ->label('Stack Trace')
                            ->disabled()
                            ->rows(10),
                    ]),
                    
                Forms\Components\Section::make('Management')
                    ->schema([
                        Forms\Components\Toggle::make('resolved')
                            ->label('Mark as Resolved'),
                        Forms\Components\TextInput::make('count')
                            ->label('Occurrence Count')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('last_seen_at')
                            ->label('Last Seen')
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('resolved')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Error Type')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->searchable()
                    ->limit(60)
                    ->wrap(),
                Tables\Columns\TextColumn::make('file')
                    ->label('File')
                    ->searchable()
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('line')
                    ->label('Line')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('count')
                    ->label('Count')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->label('Last Seen')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('First Seen')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),
            ])
            ->defaultSort('last_seen_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('resolved')
                    ->label('Status')
                    ->placeholder('All errors')
                    ->trueLabel('Resolved')
                    ->falseLabel('Unresolved')
                    ->default(false),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Error Type')
                    ->options(fn () => ErrorLog::distinct()->pluck('type', 'type')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn (ErrorLog $record) => $record->update(['resolved' => true]))
                    ->visible(fn (ErrorLog $record) => !$record->resolved)
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('resolve')
                        ->label('Mark as Resolved')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['resolved' => true]))
                        ->requiresConfirmation(),
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
            'index' => Pages\ListErrorLogs::route('/'),
            'edit' => Pages\EditErrorLog::route('/{record}/edit'),
        ];
    }
    
    public static function canEdit($record): bool
    {
        return true; // Allow editing to mark as resolved
    }
    
    public static function canDelete($record): bool
    {
        return true; // Allow deleting old resolved errors
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('resolved', false)->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('resolved', false)->count();
        return $count > 0 ? 'danger' : null;
    }
}
