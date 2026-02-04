<?php

namespace App\Core\Http\Controllers\Auth;

use App\Core\Http\Controllers\Controller;
use App\Core\Services\OAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OAuthController extends Controller
{
    public function __construct(
        protected OAuthService $oauthService
    ) {}

    /**
     * Get list of available OAuth providers
     */
    public function providers(): JsonResponse
    {
        return response()->json([
            'providers' => $this->oauthService->getSupportedProviders(),
        ]);
    }

    /**
     * Get redirect URL for OAuth provider
     */
    public function redirect(string $provider): JsonResponse
    {
        try {
            $url = $this->oauthService->getRedirectUrl($provider);

            return response()->json([
                'redirect_url' => $url,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Handle OAuth callback
     */
    public function callback(string $provider): JsonResponse
    {
        try {
            $result = $this->oauthService->handleCallback($provider);
            $user = $result['user'];

            // Update last active
            $user->update(['last_active' => now()]);

            // Create auth token
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => $user->load('currentRank', 'location', 'roles'),
                'token' => $token,
                'is_new_user' => $result['is_new'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'OAuth authentication failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Link OAuth provider to existing account
     */
    public function link(Request $request, string $provider): JsonResponse
    {
        try {
            $result = $this->oauthService->handleCallback($provider);

            // Check if this OAuth is already linked to another account
            if ($result['user']->id !== $request->user()->id) {
                return response()->json([
                    'message' => 'This OAuth account is already linked to another user.',
                ], 400);
            }

            return response()->json([
                'message' => ucfirst($provider) . ' account linked successfully.',
                'providers' => $this->oauthService->getLinkedProviders($request->user()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to link account: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Unlink OAuth provider from account
     */
    public function unlink(Request $request, string $provider): JsonResponse
    {
        $user = $request->user();

        if (!$this->oauthService->unlinkProvider($user, $provider)) {
            return response()->json([
                'message' => 'Cannot unlink. You must have a password or another OAuth provider linked.',
            ], 400);
        }

        return response()->json([
            'message' => ucfirst($provider) . ' account unlinked successfully.',
            'providers' => $this->oauthService->getLinkedProviders($user),
        ]);
    }

    /**
     * Get linked OAuth providers for authenticated user
     */
    public function linked(Request $request): JsonResponse
    {
        return response()->json([
            'providers' => $this->oauthService->getLinkedProviders($request->user()),
        ]);
    }
}
