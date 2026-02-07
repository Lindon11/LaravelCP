<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /**
     * Get current license status and details.
     */
    public function status(): JsonResponse
    {
        $key = LicenseService::getStoredKey();
        $canGenerate = LicenseService::canGenerate();

        if (!$key) {
            return response()->json([
                'licensed' => false,
                'can_generate' => $canGenerate,
                'message' => 'No license key found.',
            ]);
        }

        $result = LicenseService::validate($key);

        if (!$result['valid']) {
            return response()->json([
                'licensed' => false,
                'can_generate' => $canGenerate,
                'key' => LicenseService::maskKey($key),
                'error' => $result['error'],
            ]);
        }

        return response()->json([
            'licensed' => true,
            'can_generate' => $canGenerate,
            'key' => LicenseService::maskKey($key),
            'tier' => $result['payload']['tier'] ?? 'unknown',
            'customer' => $result['payload']['customer'] ?? 'Unknown',
            'email' => $result['payload']['email'] ?? '',
            'domain' => $result['payload']['domain'] ?? '*',
            'issued' => $result['payload']['issued'] ?? null,
            'expires' => $result['payload']['expires'] ?? 'never',
            'max_users' => $result['payload']['max_users'] ?? 'unlimited',
            'plugins' => $result['payload']['plugins'] ?? '*',
        ]);
    }

    /**
     * Activate a license key.
     */
    public function activate(Request $request): JsonResponse
    {
        $request->validate([
            'license_key' => 'required|string|min:20',
        ]);

        $key = trim($request->input('license_key'));
        $result = LicenseService::validate($key);

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 422);
        }

        // Store the key
        LicenseService::store($key);

        return response()->json([
            'success' => true,
            'message' => 'License activated successfully.',
            'tier' => $result['payload']['tier'],
            'customer' => $result['payload']['customer'],
            'expires' => $result['payload']['expires'] ?? 'never',
        ]);
    }

    /**
     * Deactivate / remove the current license key.
     */
    public function deactivate(): JsonResponse
    {
        $keyFile = storage_path('license_key');

        if (file_exists($keyFile)) {
            unlink($keyFile);
        }

        return response()->json([
            'success' => true,
            'message' => 'License deactivated.',
        ]);
    }

    /**
     * Generate a new license key (only works if private key is present).
     */
    public function generate(Request $request): JsonResponse
    {
        if (!LicenseService::canGenerate()) {
            return response()->json([
                'success' => false,
                'error' => 'License generation is not available. Private key not found.',
            ], 403);
        }

        $request->validate([
            'domain' => 'required|string|max:255',
            'tier' => 'required|in:standard,extended,unlimited',
            'customer' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'expires' => 'required|string|max:20',
            'max_users' => 'nullable|integer|min:0',
            'plugins' => 'nullable|string|max:500',
        ]);

        $key = LicenseService::generate([
            'domain' => $request->input('domain', '*'),
            'tier' => $request->input('tier', 'standard'),
            'customer' => $request->input('customer', ''),
            'email' => $request->input('email', ''),
            'expires' => $request->input('expires', 'lifetime'),
            'max_users' => (int) $request->input('max_users', 0),
            'plugins' => $request->input('plugins', 'all'),
        ]);

        if (!$key) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate license key. Check your private key.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'license_key' => $key,
            'message' => 'License key generated successfully.',
        ]);
    }
}
