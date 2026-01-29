<?php

namespace App\Filament\Pages;

use App\Services\ModuleManagerService;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class ModuleManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    
    protected static string $view = 'filament.pages.module-manager';
    
    protected static ?string $navigationLabel = 'Module Manager';
    
    protected static ?string $navigationGroup = 'Admin Panel';
    
    protected static ?int $navigationSort = 10;
    
    protected static ?string $slug = 'module-manager';
    
    public $modules = [];
    public $themes = [];
    public $activeTheme = null;
    public $activeTab = 'modules';

    public function mount(): void
    {
        $moduleManager = app(ModuleManagerService::class);
        $this->modules = $moduleManager->getAllModules();
        $this->themes = $moduleManager->getAllThemes();
        $this->activeTheme = $moduleManager->getActiveTheme();
    }

    public function installModule(string $slug): void
    {
        $moduleManager = app(ModuleManagerService::class);
        $result = $moduleManager->installModule($slug);
        
        if ($result['success']) {
            Notification::make()
                ->success()
                ->title('Module Installed')
                ->body($result['message'])
                ->send();
            
            $this->mount(); // Reload data
        } else {
            Notification::make()
                ->danger()
                ->title('Installation Failed')
                ->body($result['message'])
                ->send();
        }
    }

    public function uninstallModule(string $slug): void
    {
        $moduleManager = app(ModuleManagerService::class);
        $result = $moduleManager->uninstallModule($slug);
        
        if ($result['success']) {
            Notification::make()
                ->success()
                ->title('Module Uninstalled')
                ->body($result['message'])
                ->send();
            
            $this->mount();
        } else {
            Notification::make()
                ->danger()
                ->title('Uninstallation Failed')
                ->body($result['message'])
                ->send();
        }
    }

    public function enableModule(string $slug): void
    {
        $moduleManager = app(ModuleManagerService::class);
        $result = $moduleManager->enableModule($slug);
        
        if ($result['success']) {
            Notification::make()
                ->success()
                ->title('Module Enabled')
                ->body($result['message'])
                ->send();
            
            $this->mount();
        } else {
            Notification::make()
                ->danger()
                ->title('Enable Failed')
                ->body($result['message'])
                ->send();
        }
    }

    public function disableModule(string $slug): void
    {
        $moduleManager = app(ModuleManagerService::class);
        $result = $moduleManager->disableModule($slug);
        
        if ($result['success']) {
            Notification::make()
                ->success()
                ->title('Module Disabled')
                ->body($result['message'])
                ->send();
            
            $this->mount();
        } else {
            Notification::make()
                ->danger()
                ->title('Disable Failed')
                ->body($result['message'])
                ->send();
        }
    }

    public function installTheme(string $slug): void
    {
        $moduleManager = app(ModuleManagerService::class);
        $result = $moduleManager->installTheme($slug);
        
        if ($result['success']) {
            Notification::make()
                ->success()
                ->title('Theme Installed')
                ->body($result['message'])
                ->send();
            
            $this->mount();
        } else {
            Notification::make()
                ->danger()
                ->title('Installation Failed')
                ->body($result['message'])
                ->send();
        }
    }

    public function activateTheme(string $slug): void
    {
        $moduleManager = app(ModuleManagerService::class);
        $result = $moduleManager->activateTheme($slug);
        
        if ($result['success']) {
            Notification::make()
                ->success()
                ->title('Theme Activated')
                ->body($result['message'])
                ->send();
            
            $this->mount();
        } else {
            Notification::make()
                ->danger()
                ->title('Activation Failed')
                ->body($result['message'])
                ->send();
        }
    }
}
