<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold">Module & Theme Manager</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Install and manage modules and themes dynamically</p>
            </div>
        </div>

        <!-- Tabs -->
        <x-filament::tabs>
            <x-filament::tabs.item
                :active="$activeTab === 'modules'"
                wire:click="$set('activeTab', 'modules')"
            >
                Modules
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTab === 'themes'"
                wire:click="$set('activeTab', 'themes')"
            >
                Themes
            </x-filament::tabs.item>
        </x-filament::tabs>

        @if($activeTab === 'modules')
            <!-- Modules List -->
            <div class="space-y-4">
                @forelse($modules as $module)
                    <x-filament::card>
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold">{{ $module['name'] }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">v{{ $module['version'] }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">{{ $module['description'] ?? 'No description' }}</p>
                                @if(isset($module['author']))
                                    <p class="text-xs text-gray-400 mt-1">by {{ $module['author'] }}</p>
                                @endif
                                
                                <!-- Status Badge -->
                                <div class="mt-3">
                                    @if(!$module['installed'])
                                        <x-filament::badge color="gray">Not Installed</x-filament::badge>
                                    @elseif($module['enabled'])
                                        <x-filament::badge color="success">Enabled</x-filament::badge>
                                    @else
                                        <x-filament::badge color="warning">Disabled</x-filament::badge>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 ml-4">
                                @if(!$module['installed'])
                                    <x-filament::button
                                        wire:click="installModule('{{ $module['slug'] }}')"
                                        size="sm"
                                    >
                                        Install
                                    </x-filament::button>
                                @else
                                    @if($module['enabled'])
                                        <x-filament::button
                                            wire:click="disableModule('{{ $module['slug'] }}')"
                                            color="warning"
                                            size="sm"
                                        >
                                            Disable
                                        </x-filament::button>
                                    @else
                                        <x-filament::button
                                            wire:click="enableModule('{{ $module['slug'] }}')"
                                            color="success"
                                            size="sm"
                                        >
                                            Enable
                                        </x-filament::button>
                                    @endif

                                    <x-filament::button
                                        wire:click="uninstallModule('{{ $module['slug'] }}')"
                                        color="danger"
                                        size="sm"
                                        :disabled="$module['enabled']"
                                    >
                                        Uninstall
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>
                    </x-filament::card>
                @empty
                    <x-filament::card>
                        <p class="text-center text-gray-500 py-8">No modules available. Place modules in the /modules directory.</p>
                    </x-filament::card>
                @endforelse
            </div>
        @else
            <!-- Themes List -->
            <div class="space-y-4">
                @forelse($themes as $theme)
                    <x-filament::card>
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold">{{ $theme['name'] }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">v{{ $theme['version'] }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">{{ $theme['description'] ?? 'No description' }}</p>
                                
                                <!-- Status Badge -->
                                <div class="mt-3">
                                    @if(!$theme['installed'])
                                        <x-filament::badge color="gray">Not Installed</x-filament::badge>
                                    @elseif($theme['enabled'])
                                        <x-filament::badge color="info">Active Theme</x-filament::badge>
                                    @else
                                        <x-filament::badge color="gray">Installed</x-filament::badge>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 ml-4">
                                @if(!$theme['installed'])
                                    <x-filament::button
                                        wire:click="installTheme('{{ $theme['slug'] }}')"
                                        size="sm"
                                    >
                                        Install
                                    </x-filament::button>
                                @else
                                    @if(!$theme['enabled'])
                                        <x-filament::button
                                            wire:click="activateTheme('{{ $theme['slug'] }}')"
                                            color="success"
                                            size="sm"
                                        >
                                            Activate
                                        </x-filament::button>
                                    @endif

                                    <x-filament::button
                                        wire:click="uninstallModule('{{ $theme['slug'] }}')"
                                        color="danger"
                                        size="sm"
                                        :disabled="$theme['enabled']"
                                    >
                                        Uninstall
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>
                    </x-filament::card>
                @empty
                    <x-filament::card>
                        <p class="text-center text-gray-500 py-8">No themes available. Place themes in the /themes directory.</p>
                    </x-filament::card>
                @endforelse
            </div>
        @endif

        <!-- Info Box -->
        <x-filament::card>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <p class="font-semibold mb-2">📦 How to add modules:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Place module folders in: <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{ base_path('modules') }}</code></li>
                    <li>Each module must have a <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">module.json</code> file</li>
                    <li>See documentation: <a href="{{ asset('MODULE_MANAGER.md') }}" class="text-primary-600 hover:underline" target="_blank">MODULE_MANAGER.md</a></li>
                </ul>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
