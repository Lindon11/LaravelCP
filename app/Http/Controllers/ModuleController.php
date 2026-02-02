<?php

namespace App\Http\Controllers;

use App\Services\ModuleManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModuleController extends Controller
{
    protected $moduleManager;

    public function __construct(ModuleManagerService $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * List all available modules.
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'module');
        
        if ($type === 'theme') {
            $items = $this->moduleManager->getAllThemes();
        } else {
            $items = $this->moduleManager->getAllModules();
        }

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Upload a module/theme ZIP file.
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:zip',
            'type' => 'required|in:module,theme'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->moduleManager->uploadAndExtract(
            $request->file('file'),
            $request->input('type')
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Create a new module structure.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|alpha_dash',
            'name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->moduleManager->createModuleStructure(
            $request->input('slug'),
            $request->input('name')
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Install a module.
     */
    public function install(Request $request, $slug)
    {
        $result = $this->moduleManager->installModule($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Uninstall a module.
     */
    public function uninstall($slug)
    {
        $result = $this->moduleManager->uninstallModule($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Enable a module.
     */
    public function enable($slug)
    {
        $result = $this->moduleManager->enableModule($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Disable a module.
     */
    public function disable($slug)
    {
        $result = $this->moduleManager->disableModule($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Install a theme.
     */
    public function installTheme($slug)
    {
        $result = $this->moduleManager->installTheme($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Activate a theme (disables others).
     */
    public function activateTheme($slug)
    {
        $result = $this->moduleManager->activateTheme($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
