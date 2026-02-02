<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::orderBy('name')->get();
        
        return response()->json([
            'modules' => $modules
        ]);
    }

    public function show($id)
    {
        $module = Module::find($id);
        
        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        return response()->json($module);
    }

    public function toggle(Request $request, $id)
    {
        $module = Module::find($id);
        
        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        $module->enabled = $request->input('enabled');
        $module->save();

        return response()->json([
            'success' => true,
            'enabled' => $module->enabled
        ]);
    }

    public function update(Request $request, $id)
    {
        $module = Module::find($id);
        
        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        $module->fill($request->only(['name', 'display_name', 'description', 'enabled', 'required_level', 'icon', 'order']));
        $module->save();

        return response()->json([
            'success' => true,
            'module' => $module
        ]);
    }
}
