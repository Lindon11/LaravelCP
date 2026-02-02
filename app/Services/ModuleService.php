<?php

namespace App\Services;

use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Collection;

class ModuleService
{
    public function getEnabledModules(): Collection
    {
        return Module::where('enabled', true)
            ->orderBy('order')
            ->get();
    }

    public function getModulesForPlayer(User $player): Collection
    {
        return Module::where('enabled', true)
            ->where('required_level', '<=', $player->level ?? 1)
            ->orderBy('order')
            ->get();
    }

    /**
     * Get navigation items for dashboard
     */
    public function getNavigationItems(User $player): Collection
    {
        return $this->getModulesForPlayer($player)
            ->filter(fn($module) => !empty($module->navigation_config))
            ->map(function ($module) {
                $config = $module->navigation_config;
                
                return [
                    'name' => $module->name,
                    'display_name' => $module->display_name,
                    'description' => $module->description,
                    'icon' => $module->icon,
                    'route_name' => $module->route_name,
                    'route_url' => $module->route_name ? route($module->route_name) : null,
                    'color' => $config['color'] ?? 'bg-gray-600',
                    'order' => $module->order,
                    'section' => $config['section'] ?? 'main',
                    'icon_svg' => $config['icon_svg'] ?? null,
                ];
            })
            ->groupBy('section');
    }

    public function isModuleEnabled(string $moduleName): bool
    {
        $module = Module::where('name', $moduleName)->first();
        return $module ? $module->enabled : false;
    }

    public function canPlayerAccessModule(User $player, string $moduleName): bool
    {
        $module = Module::where('name', $moduleName)->first();
        
        if (!$module || !$module->enabled) {
            return false;
        }

        return $player->level >= $module->required_level;
    }

    public function toggleModule(string $moduleName): bool
    {
        $module = Module::where('name', $moduleName)->first();
        
        if ($module) {
            $module->enabled = !$module->enabled;
            $module->save();
            return $module->enabled;
        }

        return false;
    }

    public function updateModuleSettings(string $moduleName, array $settings): bool
    {
        $module = Module::where('name', $moduleName)->first();
        
        if ($module) {
            $module->settings = array_merge($module->settings ?? [], $settings);
            $module->save();
            return true;
        }

        return false;
    }

    public function reorderModules(array $order): void
    {
        foreach ($order as $moduleName => $position) {
            Module::where('name', $moduleName)->update(['order' => $position]);
        }
    }
}
