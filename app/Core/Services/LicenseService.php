<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\File;

class LicenseService
{
    /**
     * PUBLIC key used to VERIFY license signatures.
     * This is safe to distribute — it cannot create signatures, only verify them.
     *
     * The matching PRIVATE key is kept only in Lindon's master environment
     * and is used by the license:generate command to sign keys.
     */
    private const PUBLIC_KEY = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA549Uo/uVomEKGaemhxba
kGXTTn5kroGxQL1RdVNiHFYQfGo68jox3Mc1yDEdtjD/1h2g8jZABOuQ2EWWLNu+
2n2Gd7cUoUgoC6Fg5bEiDUdUpBgUfotX3rWBcAnBDgyA/rL+AIz01tPIJPY+OMAT
0JyJpHtpt1kaDz0gHNSkFByAkER/pJixEOkc0qBNgaAVWXpHy1u1u4z/hLow0LoO
wi04gUDzURPvWElaJrhVBJXtluUgMlZFxGnUzJjdzQ7+uECqejnk7ChJ7MZzyFXb
K3FdjEjgv4cEt0/GRk5Z3sed8XpHxgiltdkoYyhtGTKktqh3BG0YQ/LwRkgyTk+q
DwIDAQAB
-----END PUBLIC KEY-----';

    /**
     * License key format: LCP-{TIER}-{ENCODED_PAYLOAD}-{SIGNATURE}
     *
     * Payload contains: domain, tier, issued date, expiry, customer info
     * Signature is RSA-SHA256 using the private key (only Lindon can sign)
     * Verification uses the public key above (customers can verify)
     */

    /**
     * Generate a new license key using the PRIVATE key.
     * This will ONLY work if LCP_LICENSE_PRIVATE_KEY is set in .env.
     * Customers do NOT have this key — only Lindon does.
     */
    public static function generate(array $options): ?string
    {
        $privateKeyPem = env('LCP_LICENSE_PRIVATE_KEY');
        if (!$privateKeyPem) {
            // Try loading from file
            $keyPath = env('LCP_LICENSE_PRIVATE_KEY_PATH', base_path('license_private.pem'));
            if (file_exists($keyPath)) {
                $privateKeyPem = file_get_contents($keyPath);
            } else {
                return null; // No private key = cannot generate
            }
        }

        $privateKey = openssl_pkey_get_private($privateKeyPem);
        if (!$privateKey) {
            return null;
        }

        $payload = [
            'domain' => $options['domain'] ?? '*',
            'tier' => $options['tier'] ?? 'standard',
            'customer' => $options['customer'] ?? '',
            'email' => $options['email'] ?? '',
            'issued' => now()->toDateString(),
            'expires' => $options['expires'] ?? 'lifetime',
            'max_users' => $options['max_users'] ?? 0,
            'plugins' => $options['plugins'] ?? 'all',
            'id' => strtoupper(bin2hex(random_bytes(4))),
        ];

        $encodedPayload = base64_encode(json_encode($payload));

        // Sign with RSA private key
        $signature = '';
        if (!openssl_sign($encodedPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            return null;
        }

        $encodedSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        $tierCode = strtoupper(substr($payload['tier'], 0, 3));

        return "LCP-{$tierCode}-{$encodedPayload}-{$encodedSignature}";
    }

    /**
     * Validate a license key using the PUBLIC key.
     * This works on any installation — no secrets needed.
     */
    public static function validate(string $key): ?array
    {
        // Remove any whitespace/newlines that might have been introduced during copy/paste
        $key = preg_replace('/\s+/', '', $key);

        // Split into exactly 4 parts: LCP, TIER, PAYLOAD, SIGNATURE
        // The signature may contain hyphens from base64url, so we limit splits
        $firstDash = strpos($key, '-');
        if ($firstDash === false) return self::invalid('Invalid key format.');

        $secondDash = strpos($key, '-', $firstDash + 1);
        if ($secondDash === false) return self::invalid('Invalid key format.');

        $thirdDash = strpos($key, '-', $secondDash + 1);
        if ($thirdDash === false) return self::invalid('Invalid key format.');

        $prefix = substr($key, 0, $firstDash);
        $encodedPayload = substr($key, $secondDash + 1, $thirdDash - $secondDash - 1);
        $encodedSignature = substr($key, $thirdDash + 1);

        if ($prefix !== 'LCP') {
            return self::invalid('Invalid key prefix.');
        }

        // Decode signature from base64url
        $signature = base64_decode(strtr($encodedSignature, '-_', '+/'));
        if ($signature === false) {
            return self::invalid('Invalid signature encoding.');
        }

        // Verify with public key
        $publicKey = openssl_pkey_get_public(self::PUBLIC_KEY);
        if (!$publicKey) {
            return self::invalid('Public key error.');
        }

        $verified = openssl_verify($encodedPayload, $signature, $publicKey, OPENSSL_ALGO_SHA256);
        if ($verified !== 1) {
            return self::invalid('Invalid license key — signature verification failed.');
        }

        // Decode payload
        $payload = json_decode(base64_decode($encodedPayload), true);
        if (!$payload) {
            return self::invalid('Corrupted license payload.');
        }

        // Check expiry
        if (isset($payload['expires']) && $payload['expires'] !== 'lifetime') {
            if (now()->greaterThan($payload['expires'])) {
                return [
                    'valid' => false,
                    'expired' => true,
                    'error' => 'License has expired on ' . $payload['expires'] . '.',
                    'payload' => $payload,
                ];
            }
        }

        return [
            'valid' => true,
            'expired' => false,
            'payload' => $payload,
        ];
    }

    /**
     * Validate that the license matches the current domain.
     */
    public static function validateForDomain(string $key, ?string $domain = null): ?array
    {
        $result = self::validate($key);
        if (!$result || !$result['valid']) {
            return $result;
        }

        $payload = $result['payload'];
        $domain = $domain ?? request()->getHost();

        // Wildcard licenses work on any domain
        if ($payload['domain'] === '*') {
            return $result;
        }

        // Check domain match
        $licensedDomain = strtolower($payload['domain']);
        $currentDomain = strtolower($domain);

        if ($licensedDomain === $currentDomain) {
            return $result;
        }

        // Wildcard subdomain match (e.g., *.example.com)
        if (str_starts_with($licensedDomain, '*.')) {
            $baseDomain = substr($licensedDomain, 2);
            if (str_ends_with($currentDomain, $baseDomain)) {
                return $result;
            }
        }

        return [
            'valid' => false,
            'error' => "License is for domain '{$payload['domain']}', not '{$currentDomain}'.",
            'payload' => $payload,
        ];
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

        $result = self::validate($key);
        return $result && $result['valid'] && !($result['expired'] ?? false);
    }

    /**
     * Get the stored license key.
     */
    public static function getStoredKey(): ?string
    {
        $key = env('LARAVEL_CP_LICENSE');
        if ($key) {
            return $key;
        }

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
                'error' => 'No license key found.',
            ];
        }

        $result = self::validateForDomain($key);
        if (!$result) {
            return [
                'valid' => false,
                'error' => 'Invalid license key.',
                'key' => self::maskKey($key),
            ];
        }

        $result['key'] = self::maskKey($key);
        return $result;
    }

    /**
     * Store a license key.
     */
    public static function store(string $key): bool
    {
        $result = self::validate($key);
        if (!$result || !$result['valid']) {
            return false;
        }

        File::put(storage_path('license_key'), $key);
        return true;
    }

    /**
     * Mask a license key for display.
     */
    public static function maskKey(string $key): string
    {
        if (strlen($key) <= 20) {
            return $key;
        }

        return substr($key, 0, 12) . '...' . substr($key, -8);
    }

    /**
     * Check if this environment can generate license keys (has private key).
     */
    public static function canGenerate(): bool
    {
        if (env('LCP_LICENSE_PRIVATE_KEY')) {
            return true;
        }

        $keyPath = env('LCP_LICENSE_PRIVATE_KEY_PATH', base_path('license_private.pem'));
        return file_exists($keyPath);
    }

    /**
     * Generate an RSA keypair for initial setup.
     * Run this ONCE on your machine, then save the private key securely.
     */
    public static function generateKeypair(): ?array
    {
        $config = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $key = openssl_pkey_new($config);
        if (!$key) {
            return null;
        }

        openssl_pkey_export($key, $privateKey);
        $details = openssl_pkey_get_details($key);
        $publicKey = $details['key'];

        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey,
        ];
    }

    /**
     * Return a null/invalid result helper.
     */
    private static function invalid(string $error): array
    {
        return [
            'valid' => false,
            'error' => $error,
        ];
    }
}
