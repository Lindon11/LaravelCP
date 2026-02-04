<?php

namespace App\Core\Services;

use App\Core\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Get a setting value
     */
    public function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return $setting->getCastedValue();
        });
    }

    /**
     * Set a setting value (creates if doesn't exist)
     */
    public function set(string $key, $value, ?string $description = null, string $group = 'general'): Setting
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        
        $setting->setCastedValue($value);
        
        if ($description) {
            $setting->description = $description;
        }
        
        $setting->group = $group;
        $setting->save();
        
        // Clear cache
        Cache::forget("setting.{$key}");
        
        return $setting;
    }

    /**
     * Check if a setting exists
     */
    public function has(string $key): bool
    {
        return Setting::where('key', $key)->exists();
    }

    /**
     * Delete a setting
     */
    public function forget(string $key): bool
    {
        Cache::forget("setting.{$key}");
        return Setting::where('key', $key)->delete();
    }

    /**
     * Get all settings in a group
     */
    public function getGroup(string $group): array
    {
        $settings = Setting::where('group', $group)->get();
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->getCastedValue();
        }
        
        return $result;
    }

    /**
     * Get all settings as key-value pairs
     */
    public function all(): array
    {
        return Cache::remember('settings.all', 3600, function () {
            $settings = Setting::all();
            
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = $setting->getCastedValue();
            }
            
            return $result;
        });
    }

    /**
     * Clear all settings cache
     */
    public function clearCache(): void
    {
        Cache::forget('settings.all');
        
        $keys = Setting::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
    }
}
