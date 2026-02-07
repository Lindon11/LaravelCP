<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\File;

class LicenseService
{
    /**
     * The signing secret used to generate and verify license keys.
     * This should ONLY exist on your master/generation environment.
     */
    private const SIGNING_SECRET = 'LCP-SIGNING-f7e2a9c4d1b8e3f5a6c7d8e9f0a1b2c3';

    /**
     * License key format: LCP-{TIER}-{ENCODED_PAYLOAD}-{SIGNATURE}
     * 
     * Payload contains: domain, tier, issued date, expiry, customer email
     * Signature is HMAC-SHA256 of the payload using the signing secret
     */

    /**
     * Generate a new license key.
     * This method should only be called from your admin/CLI environment.
     */
    public static function generate(array $options): string
    {
        $payload = [
            'domain' => $options['domain'] ?? '*',
            'tier' => $options['tier'] ?? 'standard', // standard, extended, unlimited
            'customer' => $options['customer'] ?? '',
            'email' => $options['email'] ?? '',
            'issued' => now()->toDateString(),
            'expires' => $options['expires'] ?? 'lifetime',
            'max_users' => $options['max_users'] ?? 0, // 0 = unlimited
            'plugins' => $options['plugins'] ?? 'all', // all, or comma-separated list
            'id' => strtoupper(bin2hex(random_bytes(4))), // unique license ID
        ];

        $encodedPayload = base64_encode(json_encode($payload));
        $signature = self::sign($encodedPayload);
        $tierCode = strtoupper(substr($payload['tier'], 0, 3));

        return "LCP-{$tierCode}-{$encodedPayload}-{$signature}";
    }

    /**
     * Validate a license key and return the decoded payload.
     * Returns null if invalid.
     */
    public static function validate(string $key): ?array
    {
        $parts = explode('-', $key, 4);

        if (count($parts) !== 4 || $parts[0] !== 'LCP') {
            return null;
        }

        $encodedPayload = $parts[2];
        $providedSignature = $parts[3];

        // Verify signature
        $expectedSignature = self::sign($encodedPayload);
        if (!hash_equals($expectedSignature, $providedSignature)) {
            return null;
        }

        // Decode payload
        $payload = json_decode(base64_decode($encodedPayload), true);
        if (!$payload) {
            return null;
        }

        // Check expiry
        if ($payload['expires'] !== 'lifetime') {
            if (now()->greaterThan($payload['expires'])) {
                $payload['expired'] = true;
            }
        }

        $payload['valid'] = true;
        $payload['expired'] = $payload['expired'] ?? false;

        return $payload;
    }

    /**
     * Validate that the license matches the current domain.
     */
    public static function validateForDomain(string $key, ?string $domain = null): ?array
    {
        $payload = self::validate($key);
        if (!$payload) {
            return null;
        }

        $domain = $domain ?? request()->getHost();

        // Wildcard licenses work on any domain
        if ($payload['domain'] === '*') {
            return $payload;
        }

        // Check domain match (supports wildcard subdomains)
        $licensedDomain = strtolower($payload['domain']);
        $currentDomain = strtolower($domain);

        if ($licensedDomain === $currentDomain) {
            return $payload;
        }

        // Check if wildcard subdomain matches (e.g., *.example.com)
        if (str_starts_with($licensedDomain, '*.')) {
            $baseDomain = substr($licensedDomain, 2);
            if (str_ends_with($currentDomain, $baseDomain)) {
                return $payload;
            }
        }

        $payload['domain_mismatch'] = true;
        return $payload;
    }

    /**
     * Check if a valid license is stored on this installation.
     */
    public static function isLicensed(): bool
    {
        $key = self::getStoredKey();
        if (!$key) {
            return false;
        }

        $payload = self::validate($key);
        return $payload && $payload['valid'] && !$payload['expired'];
    }

    /**
     * Get the stored license key.
     */
    public static function getStoredKey(): ?string
    {
        // Check env first
        $key = env('LARAVEL_CP_LICENSE');
        if ($key) {
            return $key;
        }

        // Check license file
        $path = storage_path('license_key');
        if (File::exists($path)) {
            return trim(File::get($path));
        }

        return null;
    }

    /**
     * Get full license details for the current installation.
     */
    public static function getDetails(): array
    {
        $key = self::getStoredKey();
        if (!$key) {
            return [
                'valid' => false,
                'message' => 'No license key found.',
            ];
        }

        $payload = self::validateForDomain($key);
        if (!$payload) {
            return [
                'valid' => false,
                'message' => 'Invalid license key.',
                'key' => self::maskKey($key),
            ];
        }

        if ($payload['expired'] ?? false) {
            return array_merge($payload, [
                'valid' => false,
                'message' => 'License has expired.',
                'key' => self::maskKey($key),
            ]);
        }

        if ($payload['domain_mismatch'] ?? false) {
            return array_merge($payload, [
                'valid' => false,
                'message' => "License is not valid for this domain. Licensed for: {$payload['domain']}",
                'key' => self::maskKey($key),
            ]);
        }

        return array_merge($payload, [
            'message' => 'License is valid.',
            'key' => self::maskKey($key),
        ]);
    }

    /**
     * Store a license key.
     */
    public static function store(string $key): bool
    {
        $payload = self::validate($key);
        if (!$payload || !$payload['valid']) {
            return false;
        }

        File::put(storage_path('license_key'), $key);
        return true;
    }

    /**
     * Mask a license key for display (show first and last 8 chars).
     */
    public static function maskKey(string $key): string
    {
        if (strlen($key) <= 20) {
            return $key;
        }

        return substr($key, 0, 12) . '...' . substr($key, -8);
    }

    /**
     * Create HMAC signature.
     */
    private static function sign(string $data): string
    {
        return substr(hash_hmac('sha256', $data, self::SIGNING_SECRET), 0, 16);
    }
}
