<?php

namespace App\Console\Commands;

use App\Core\Services\LicenseService;
use Illuminate\Console\Command;

class ValidateLicense extends Command
{
    protected $signature = 'license:validate
        {key? : License key to validate (omit to check stored key)}';

    protected $description = 'Validate a LaravelCP license key';

    public function handle(): int
    {
        $key = $this->argument('key');

        if (!$key) {
            $key = LicenseService::getStoredKey();
            if (!$key) {
                $this->error('No license key found. Provide one as an argument or store it.');
                return Command::FAILURE;
            }
            $this->info('Checking stored license key...');
        }

        $result = LicenseService::validateForDomain($key);

        $this->newLine();

        if (!$result || !$result['valid']) {
            $error = $result['error'] ?? 'Invalid license key.';
            $this->error("✗ {$error}");

            // Still show details if payload was decoded
            if (isset($result['payload'])) {
                $this->showPayload($result['payload']);
            }

            return Command::FAILURE;
        }

        $this->info('✓ License is VALID');
        $this->showPayload($result['payload']);

        return Command::SUCCESS;
    }

    private function showPayload(array $payload): void
    {
        $this->newLine();
        $this->table(
            ['Property', 'Value'],
            [
                ['License ID', $payload['id'] ?? '-'],
                ['Domain', $payload['domain'] ?? '-'],
                ['Tier', ucfirst($payload['tier'] ?? 'unknown')],
                ['Customer', $payload['customer'] ?: '-'],
                ['Email', $payload['email'] ?: '-'],
                ['Issued', $payload['issued'] ?? '-'],
                ['Expires', $payload['expires'] ?? '-'],
                ['Max Users', ($payload['max_users'] ?? 0) === 0 ? 'Unlimited' : $payload['max_users']],
                ['Plugins', $payload['plugins'] ?? 'all'],
            ]
        );
    }
}
