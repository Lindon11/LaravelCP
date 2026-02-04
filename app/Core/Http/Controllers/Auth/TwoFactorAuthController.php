<?php

namespace App\Core\Http\Controllers\Auth;

use App\Core\Http\Controllers\Controller;
use App\Core\Services\TwoFactorAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorAuthController extends Controller
{
    public function __construct(
        protected TwoFactorAuthService $twoFactorService
    ) {}

    /**
     * Get 2FA status for the authenticated user
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'enabled' => $this->twoFactorService->isEnabled($user),
            'confirmed_at' => $user->two_factor_confirmed_at,
        ]);
    }

    /**
     * Generate a new 2FA secret and QR code
     */
    public function setup(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($this->twoFactorService->isEnabled($user)) {
            return response()->json([
                'message' => 'Two-factor authentication is already enabled.',
            ], 400);
        }

        $secret = $this->twoFactorService->generateSecretKey();
        $qrCodeUrl = $this->twoFactorService->getQrCodeUrl($user, $secret);

        // Store secret temporarily in session or encrypted cookie
        session(['2fa_secret' => $secret]);

        // Generate QR code as SVG
        $qrCode = QrCode::format('svg')
            ->size(200)
            ->generate($qrCodeUrl);

        return response()->json([
            'secret' => $secret,
            'qr_code' => base64_encode($qrCode),
            'qr_code_url' => $qrCodeUrl,
        ]);
    }

    /**
     * Confirm and enable 2FA
     */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $secret = session('2fa_secret');

        if (!$secret) {
            return response()->json([
                'message' => 'No 2FA setup in progress. Please start setup again.',
            ], 400);
        }

        if (!$this->twoFactorService->enable($user, $secret, $request->code)) {
            return response()->json([
                'message' => 'Invalid verification code.',
            ], 422);
        }

        session()->forget('2fa_secret');

        return response()->json([
            'message' => 'Two-factor authentication has been enabled.',
            'recovery_codes' => $this->twoFactorService->getRecoveryCodes($user),
        ]);
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string|current_password',
        ]);

        $user = $request->user();

        if (!$this->twoFactorService->isEnabled($user)) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled.',
            ], 400);
        }

        $this->twoFactorService->disable($user);

        return response()->json([
            'message' => 'Two-factor authentication has been disabled.',
        ]);
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'code' => 'required|string',
        ]);

        $user = \App\Core\Models\User::findOrFail($request->user_id);

        if (!$this->twoFactorService->verifyCodeOrRecovery($user, $request->code)) {
            return response()->json([
                'message' => 'Invalid verification code.',
            ], 422);
        }

        // Update last active
        $user->update(['last_active' => now()]);

        // Create auth token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load('currentRank', 'location', 'roles'),
            'token' => $token,
        ]);
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string|current_password',
        ]);

        $user = $request->user();

        if (!$this->twoFactorService->isEnabled($user)) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled.',
            ], 400);
        }

        $codes = $this->twoFactorService->regenerateRecoveryCodes($user);

        return response()->json([
            'recovery_codes' => $codes,
        ]);
    }

    /**
     * Get current recovery codes
     */
    public function recoveryCodes(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string|current_password',
        ]);

        $user = $request->user();

        if (!$this->twoFactorService->isEnabled($user)) {
            return response()->json([
                'message' => 'Two-factor authentication is not enabled.',
            ], 400);
        }

        return response()->json([
            'recovery_codes' => $this->twoFactorService->getRecoveryCodes($user),
        ]);
    }
}
