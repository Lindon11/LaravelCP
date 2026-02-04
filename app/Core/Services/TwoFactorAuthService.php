<?php

namespace App\Core\Services;

use App\Core\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new secret key for 2FA
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Get the QR code URL for setting up 2FA
     */
    public function getQrCodeUrl(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
    }

    /**
     * Generate recovery codes
     */
    public function generateRecoveryCodes(): Collection
    {
        return Collection::times(8, function () {
            return Str::random(10) . '-' . Str::random(10);
        });
    }

    /**
     * Verify a 2FA code
     */
    public function verify(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Enable 2FA for a user
     */
    public function enable(User $user, string $secret, string $code): bool
    {
        if (!$this->verify($secret, $code)) {
            return false;
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt($recoveryCodes->toJson()),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return true;
    }

    /**
     * Disable 2FA for a user
     */
    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    /**
     * Check if user has 2FA enabled
     */
    public function isEnabled(User $user): bool
    {
        return !is_null($user->two_factor_secret) && !is_null($user->two_factor_confirmed_at);
    }

    /**
     * Verify code or recovery code
     */
    public function verifyCodeOrRecovery(User $user, string $code): bool
    {
        // First try normal 2FA code
        $secret = decrypt($user->two_factor_secret);
        if ($this->verify($secret, $code)) {
            return true;
        }

        // Try recovery codes
        return $this->useRecoveryCode($user, $code);
    }

    /**
     * Use a recovery code
     */
    public function useRecoveryCode(User $user, string $code): bool
    {
        $recoveryCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));

        if (!$recoveryCodes->contains($code)) {
            return false;
        }

        // Remove used recovery code
        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(
                $recoveryCodes->reject(fn($c) => $c === $code)->values()->toJson()
            ),
        ])->save();

        return true;
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(User $user): Collection
    {
        $codes = $this->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt($codes->toJson()),
        ])->save();

        return $codes;
    }

    /**
     * Get recovery codes for display
     */
    public function getRecoveryCodes(User $user): array
    {
        if (!$user->two_factor_recovery_codes) {
            return [];
        }

        return json_decode(decrypt($user->two_factor_recovery_codes), true);
    }
}
