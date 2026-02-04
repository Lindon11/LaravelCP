<?php

namespace App\Core\Http\Controllers;

use App\Core\Services\PluginManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PluginController extends Controller
{
    protected $pluginManager;

    public function __construct(PluginManagerService $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * List all available plugins.
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'plugin');

        if ($type === 'theme') {
            $items = $this->pluginManager->getAllThemes();
        } else {
            $installed = $this->pluginManager->getAllPlugins();
            $staging = $this->pluginManager->getStagingPlugins();
            $disabled = $this->pluginManager->getDisabledPlugins();

            $items = array_merge($installed, $staging, $disabled);
        }

        return response()->json([
            'success' => true,
            'plugins' => $items
        ]);
    }

    /**
     * Upload a plugin/theme ZIP file.
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:zip',
            'type' => 'required|in:plugin,theme'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->pluginManager->uploadAndExtract(
            $request->file('file'),
            $request->input('type')
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Create a new plugin structure.
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

        $result = $this->pluginManager->createPluginStructure(
            $request->input('slug'),
            $request->input('name')
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Install a plugin.
     */
    public function install(Request $request, $slug)
    {
        $result = $this->pluginManager->installPlugin($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Uninstall a plugin.
     */
    public function uninstall($slug)
    {
        $result = $this->pluginManager->uninstallPlugin($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Enable a plugin.
     */
    public function enable($slug)
    {
        $result = $this->pluginManager->enablePlugin($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Disable a plugin.
     */
    public function disable($slug)
    {
        $result = $this->pluginManager->disablePlugin($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Reactivate a disabled plugin.
     */
    public function reactivate($slug)
    {
        $result = $this->pluginManager->reactivatePlugin($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Remove a plugin from staging.
     */
    public function removeStaging($slug)
    {
        $result = $this->pluginManager->removeStagingPlugin($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Install a theme.
     */
    public function installTheme($slug)
    {
        $result = $this->pluginManager->installTheme($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Activate a theme (disables others).
     */
    public function activateTheme($slug)
    {
        $result = $this->pluginManager->activateTheme($slug);
        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
