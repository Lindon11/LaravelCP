<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get all settings as a flat key-value object
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        
        // Convert boolean strings to actual booleans for frontend
        foreach ($settings as $key => $value) {
            if ($value === '1' || $value === 'true') {
                $settings[$key] = true;
            } elseif ($value === '0' || $value === 'false') {
                $settings[$key] = false;
            } elseif (is_numeric($value) && strpos($value, '.') !== false) {
                $settings[$key] = (float) $value;
            } elseif (is_numeric($value)) {
                $settings[$key] = (int) $value;
            }
        }
        
        return response()->json($settings);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        // Check if settings are sent as flat object (from frontend) or array format
        $data = $request->all();
        
        // If data has a 'settings' array with key/value pairs, use that format
        if (isset($data['settings']) && is_array($data['settings'])) {
            // Check if it's the old format: [{key: 'x', value: 'y'}]
            if (isset($data['settings'][0]['key'])) {
                $validated = $request->validate([
                    'settings' => 'required|array',
                    'settings.*.key' => 'required|string',
                    'settings.*.value' => 'required',
                ]);

                foreach ($validated['settings'] as $setting) {
                    Setting::updateOrCreate(
                        ['key' => $setting['key']],
                        ['value' => $setting['value']]
                    );
                }
            }
        } else {
            // Flat object format from frontend: {game_name: 'x', starting_cash: 1000}
            // Exclude any non-setting fields
            $excludeFields = ['_token', '_method'];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $excludeFields)) {
                    continue;
                }
                
                // Convert boolean to string for storage
                if (is_bool($value)) {
                    $value = $value ? '1' : '0';
                }
                
                // Convert arrays/objects to JSON
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => (string) $value]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully'
        ]);
    }

    /**
     * Get specific setting
     */
    public function show($key)
    {
        $setting = Setting::where('key', $key)->firstOrFail();
        return response()->json($setting);
    }

    /**
     * Create or update settings (handles both single setting and bulk update)
     */
    public function store(Request $request)
    {
        $data = $request->all();
        
        // Check if this is a single setting with 'key' field
        if (isset($data['key'])) {
            $validated = $request->validate([
                'key' => 'required|string',
                'value' => 'required',
                'category' => 'nullable|string',
                'type' => 'nullable|string|in:text,number,boolean,json',
                'description' => 'nullable|string',
            ]);

            $setting = Setting::updateOrCreate(
                ['key' => $validated['key']],
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Setting saved successfully',
                'setting' => $setting
            ]);
        }
        
        // Otherwise, treat as bulk update with flat object format
        // {game_name: 'x', starting_cash: 1000, ...}
        $excludeFields = ['_token', '_method'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $excludeFields)) {
                continue;
            }
            
            // Convert boolean to string for storage
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            
            // Convert arrays/objects to JSON
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully'
        ]);
    }

    /**
     * Delete setting
     */
    public function destroy($key)
    {
        Setting::where('key', $key)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully'
        ]);
    }
}
