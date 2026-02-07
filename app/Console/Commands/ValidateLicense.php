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

        $details = LicenseService::validateForDomain($key);

        if (!$details) {
            $this->error('✗ Invalid license key. Signature verification failed.');
            return Command::FAILURE;
        }

        $this->newLine();

        if ($details['expired'] ?? false) {
            $this->error('✗ License has EXPIRED');
        } elseif ($details['domain_mismatch'] ?? false) {
            $this->warn("⚠ License domain mismatch. Licensed for: {$details['domain']}");
        } else {
            $this->info('✓ License is VALID');
        }

        $this->newLine();
        $this->table(
            ['Property', 'Value'],
            [
                ['License ID', $details['id'] ?? '-'],
                ['Domain', $details['domain']],
                ['Tier', ucfirst($details['tier'])],
                ['Customer', $details['customer'] ?: '-'],
                ['Email', $details['email'] ?: '-'],
                ['Issued', $details['issued']],
                ['Expires', $details['expires']],
                ['Max Users', ($details['max_users'] ?? 0) === 0 ? 'Unlimited' : $details['max_users']],
                ['Plugins', $details['plugins'] ?? 'all'],
            ]
        );

        return ($details['valid'] ?? false) ? Command::SUCCESS : Command::FAILURE;
    }
}
