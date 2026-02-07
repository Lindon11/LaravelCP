<?php

namespace App\Console\Commands;

use App\Core\Services\LicenseService;
use Illuminate\Console\Command;

class LicenseSetup extends Command
{
    protected $signature = 'license:setup
        {--force : Overwrite existing keypair}';

    protected $description = 'Generate RSA keypair for license signing (run once on your machine only)';

    public function handle(): int
    {
        $privateKeyPath = base_path('license_private.pem');
        $publicKeyPath = base_path('license_public.pem');

        if (file_exists($privateKeyPath) && !$this->option('force')) {
            $this->error('Private key already exists at: ' . $privateKeyPath);
            $this->line('Use --force to overwrite.');
            return Command::FAILURE;
        }

        $this->warn('');
        $this->warn('  ⚠  This generates an RSA keypair for license signing.');
        $this->warn('  ⚠  The PRIVATE KEY must NEVER be shared or committed to git.');
        $this->warn('  ⚠  Run this ONCE on your own machine only.');
        $this->warn('');

        if (!$this->confirm('Continue?')) {
            return Command::SUCCESS;
        }

        $keypair = LicenseService::generateKeypair();
        if (!$keypair) {
            $this->error('Failed to generate keypair. Ensure OpenSSL is available.');
            return Command::FAILURE;
        }

        // Save private key
        file_put_contents($privateKeyPath, $keypair['private_key']);
        chmod($privateKeyPath, 0600); // Owner read-only

        // Save public key (for reference)
        file_put_contents($publicKeyPath, $keypair['public_key']);

        $this->newLine();
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║      RSA Keypair Generated Successfully  ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        $this->line("  <fg=green>Private key saved to:</> <fg=yellow>{$privateKeyPath}</>");
        $this->line("  <fg=green>Public key saved to:</>  <fg=yellow>{$publicKeyPath}</>");
        $this->newLine();

        $this->warn('  IMPORTANT — Next steps:');
        $this->newLine();
        $this->line('  1. The public key below must be embedded in LicenseService.php');
        $this->line('     (replace the placeholder PUBLIC_KEY constant)');
        $this->newLine();
        $this->line('  2. Add these to <fg=yellow>.gitignore</>:');
        $this->line('     license_private.pem');
        $this->line('     license_public.pem');
        $this->newLine();
        $this->line('  3. Back up <fg=red>license_private.pem</> securely — if you lose it,');
        $this->line('     you cannot generate new license keys.');
        $this->newLine();

        $this->info('  Public Key (copy into LicenseService.php):');
        $this->newLine();
        $this->line($keypair['public_key']);

        return Command::SUCCESS;
    }
}
