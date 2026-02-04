<?php

namespace App\Core\Services;

use App\Core\Models\User;
use App\Core\Models\OAuthProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class OAuthService
{
    /**
     * Supported OAuth providers
     */
    protected array $supportedProviders = [
        'discord',
        'google',
        'github',
        'twitter',
        'facebook',
    ];

    /**
     * Get redirect URL for OAuth provider
     */
    public function getRedirectUrl(string $provider): string
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)
            ->stateless()
            ->redirect()
            ->getTargetUrl();
    }

    /**
     * Handle OAuth callback and return or create user
     */
    public function handleCallback(string $provider): array
    {
        $this->validateProvider($provider);

        $socialiteUser = Socialite::driver($provider)->stateless()->user();

        // Check if OAuth account is already linked
        $oauthProvider = OAuthProvider::where('provider', $provider)
            ->where('provider_id', $socialiteUser->getId())
            ->first();

        if ($oauthProvider) {
            // Update OAuth token info
            $oauthProvider->update([
                'token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken ?? null,
                'expires_at' => isset($socialiteUser->expiresIn) 
                    ? now()->addSeconds($socialiteUser->expiresIn) 
                    : null,
            ]);

            return [
                'user' => $oauthProvider->user,
                'is_new' => false,
            ];
        }

        // Check if user exists with same email
        $user = User::where('email', $socialiteUser->getEmail())->first();

        if ($user) {
            // Link OAuth provider to existing user
            $this->linkProvider($user, $provider, $socialiteUser);

            return [
                'user' => $user,
                'is_new' => false,
            ];
        }

        // Create new user
        $user = $this->createUserFromSocialite($provider, $socialiteUser);

        return [
            'user' => $user,
            'is_new' => true,
        ];
    }

    /**
     * Link OAuth provider to existing user
     */
    public function linkProvider(User $user, string $provider, SocialiteUser $socialiteUser): OAuthProvider
    {
        $this->validateProvider($provider);

        // Check if already linked
        $existing = $user->oauthProviders()
            ->where('provider', $provider)
            ->first();

        if ($existing) {
            $existing->update([
                'provider_id' => $socialiteUser->getId(),
                'token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken ?? null,
                'expires_at' => isset($socialiteUser->expiresIn) 
                    ? now()->addSeconds($socialiteUser->expiresIn) 
                    : null,
                'avatar' => $socialiteUser->getAvatar(),
                'nickname' => $socialiteUser->getNickname(),
            ]);

            return $existing;
        }

        return $user->oauthProviders()->create([
            'provider' => $provider,
            'provider_id' => $socialiteUser->getId(),
            'token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken ?? null,
            'expires_at' => isset($socialiteUser->expiresIn) 
                ? now()->addSeconds($socialiteUser->expiresIn) 
                : null,
            'avatar' => $socialiteUser->getAvatar(),
            'nickname' => $socialiteUser->getNickname(),
        ]);
    }

    /**
     * Unlink OAuth provider from user
     */
    public function unlinkProvider(User $user, string $provider): bool
    {
        // Ensure user has password or another OAuth provider
        if (!$user->password && $user->oauthProviders()->count() <= 1) {
            return false;
        }

        return $user->oauthProviders()
            ->where('provider', $provider)
            ->delete() > 0;
    }

    /**
     * Get linked OAuth providers for user
     */
    public function getLinkedProviders(User $user): array
    {
        return $user->oauthProviders()
            ->get()
            ->map(fn($p) => [
                'provider' => $p->provider,
                'nickname' => $p->nickname,
                'avatar' => $p->avatar,
                'linked_at' => $p->created_at,
            ])
            ->toArray();
    }

    /**
     * Get all supported providers
     */
    public function getSupportedProviders(): array
    {
        return collect($this->supportedProviders)
            ->filter(fn($provider) => $this->isProviderConfigured($provider))
            ->values()
            ->toArray();
    }

    /**
     * Check if provider is configured
     */
    public function isProviderConfigured(string $provider): bool
    {
        return !empty(config("services.{$provider}.client_id"));
    }

    /**
     * Validate provider is supported
     */
    protected function validateProvider(string $provider): void
    {
        if (!in_array($provider, $this->supportedProviders)) {
            throw new \InvalidArgumentException("Unsupported OAuth provider: {$provider}");
        }

        if (!$this->isProviderConfigured($provider)) {
            throw new \InvalidArgumentException("OAuth provider not configured: {$provider}");
        }
    }

    /**
     * Create new user from Socialite user
     */
    protected function createUserFromSocialite(string $provider, SocialiteUser $socialiteUser): User
    {
        // Generate unique username
        $baseUsername = $socialiteUser->getNickname() 
            ?? Str::slug(explode('@', $socialiteUser->getEmail())[0]);
        $username = $this->generateUniqueUsername($baseUsername);

        $user = User::create([
            'name' => $socialiteUser->getName() ?? $username,
            'username' => $username,
            'email' => $socialiteUser->getEmail(),
            'password' => null, // No password for OAuth-only users
            'email_verified_at' => now(), // Trust OAuth email verification
            'rank_id' => 1,
            'location_id' => 1,
            'energy' => 100,
            'max_energy' => 100,
            'health' => 100,
            'max_health' => 100,
            'cash' => 1000,
            'bank' => 0,
            'bullets' => 50,
            'experience' => 0,
            'level' => 1,
        ]);

        // Assign default role
        if (\Spatie\Permission\Models\Role::where('name', 'user')->exists()) {
            $user->assignRole('user');
        }

        // Link OAuth provider
        $user->oauthProviders()->create([
            'provider' => $provider,
            'provider_id' => $socialiteUser->getId(),
            'token' => $socialiteUser->token,
            'refresh_token' => $socialiteUser->refreshToken ?? null,
            'expires_at' => isset($socialiteUser->expiresIn) 
                ? now()->addSeconds($socialiteUser->expiresIn) 
                : null,
            'avatar' => $socialiteUser->getAvatar(),
            'nickname' => $socialiteUser->getNickname(),
        ]);

        return $user;
    }

    /**
     * Generate unique username
     */
    protected function generateUniqueUsername(string $base): string
    {
        $username = Str::slug($base, '_');
        $original = $username;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $original . '_' . $counter;
            $counter++;
        }

        return $username;
    }
}
