<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get all settings grouped by category
     */
    public function index()
    {
        $settings = Setting::all()->groupBy('category');
        return response()->json($settings);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
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
     * Create or update single setting
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'required',
            'category' => 'required|string',
            'type' => 'required|string|in:text,number,boolean,json',
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
