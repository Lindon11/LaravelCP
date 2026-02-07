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

        if (!$key) {
            return response()->json([
                'licensed' => false,
                'message' => 'No license key found.',
            ]);
        }

        $result = LicenseService::validate($key);

        if (!$result['valid']) {
            return response()->json([
                'licensed' => false,
                'key' => LicenseService::maskKey($key),
                'error' => $result['error'],
            ]);
        }

        return response()->json([
            'licensed' => true,
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
}
