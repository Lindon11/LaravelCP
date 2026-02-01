<?php

use App\Services\SettingService;

if (!function_exists('setting')) {
    /**
     * Get or set a setting value
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed|SettingService
     */
    function setting(?string $key = null, $default = null)
    {
        $service = app(SettingService::class);
        
        if ($key === null) {
            return $service;
        }
        
        return $service->get($key, $default);
    }
}
