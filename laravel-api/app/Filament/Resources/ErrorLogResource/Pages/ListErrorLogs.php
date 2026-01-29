<?php

namespace App\Filament\Resources\ErrorLogResource\Pages;

use App\Filament\Resources\ErrorLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class ListErrorLogs extends ListRecords
{
    protected static string $resource = ErrorLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import_logs')
                ->label('Import from Logs')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Import Errors from Log Files')
                ->modalDescription('This will scan Laravel log files and import any errors found. Duplicate errors will be updated with increased counts.')
                ->modalSubmitActionLabel('Import Errors')
                ->action(function () {
                    try {
                        // Run the import command
                        Artisan::call('logs:import-errors', ['--days' => 30]);
                        $output = Artisan::output();
                        
                        // Parse output for statistics
                        preg_match('/Imported: (\d+)/', $output, $importedMatch);
                        preg_match('/Updated: (\d+)/', $output, $updatedMatch);
                        
                        $imported = $importedMatch[1] ?? 0;
                        $updated = $updatedMatch[1] ?? 0;
                        
                        Notification::make()
                            ->success()
                            ->title('Logs imported successfully')
                            ->body("Imported {$imported} new errors, updated {$updated} existing errors.")
                            ->send();
                            
                        // Refresh the page to show new errors
                        $this->redirect(static::getUrl());
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Import failed')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
            Actions\Action::make('auto_resolve')
                ->label('Auto-Resolve Old')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Auto-Resolve Old Errors')
                ->modalDescription('Automatically mark errors as resolved if they haven\'t occurred in the last 7 days.')
                ->modalSubmitActionLabel('Auto-Resolve')
                ->action(function () {
                    try {
                        Artisan::call('errors:auto-resolve', ['--days' => 7]);
                        $output = Artisan::output();
                        
                        preg_match('/Auto-resolved (\d+)/', $output, $match);
                        $resolved = $match[1] ?? 0;
                        
                        Notification::make()
                            ->success()
                            ->title('Errors auto-resolved')
                            ->body("Marked {$resolved} old error(s) as resolved.")
                            ->send();
                            
                        $this->redirect(static::getUrl());
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Auto-resolve failed')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
            Actions\Action::make('clear_resolved')
                ->label('Clear Resolved')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Delete All Resolved Errors')
                ->modalDescription('This will permanently delete all errors marked as resolved. This action cannot be undone.')
                ->modalSubmitActionLabel('Delete Resolved Errors')
                ->action(function () {
                    $count = \App\Models\ErrorLog::where('resolved', true)->count();
                    \App\Models\ErrorLog::where('resolved', true)->delete();
                    
                    Notification::make()
                        ->success()
                        ->title('Resolved errors cleared')
                        ->body("Deleted {$count} resolved errors.")
                        ->send();
                }),
        ];
    }
}
