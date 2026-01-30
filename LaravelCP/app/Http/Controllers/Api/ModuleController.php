<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ModuleRegistry;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        return response()->json([
            'modules' => ModuleRegistry::all()
        ]);
    }

    public function show($id)
    {
        $module = ModuleRegistry::find($id);
        
        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        return response()->json($module);
    }

    public function toggle(Request $request, $id)
    {
        $module = ModuleRegistry::find($id);
        
        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // In a real implementation, you'd save this to database
        // For now, just return success
        return response()->json([
            'success' => true,
            'enabled' => $request->input('enabled')
        ]);
    }

    public function update(Request $request, $id)
    {
        $module = ModuleRegistry::find($id);
        
        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        // In a real implementation, you'd save to database
        return response()->json([
            'success' => true,
            'module' => array_merge($module, $request->all())
        ]);
    }
}
