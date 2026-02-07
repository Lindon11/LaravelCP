<?php

namespace App\Console\Commands;

use App\Core\Services\LicenseService;
use Illuminate\Console\Command;

class GenerateLicense extends Command
{
    protected $signature = 'license:generate
        {--domain= : Domain to lock the license to (default: * for any)}
        {--tier=standard : License tier (standard, extended, unlimited)}
        {--customer= : Customer name}
        {--email= : Customer email}
        {--expires= : Expiry date (YYYY-MM-DD) or "lifetime"}
        {--max-users=0 : Max users (0 = unlimited)}
        {--plugins=all : Allowed plugins (all, or comma-separated)}';

    protected $description = 'Generate a new LaravelCP license key';

    public function handle(): int
    {
        $domain = $this->option('domain') ?: $this->ask('Domain (e.g., example.com, *.example.com, or * for any)', '*');
        $tier = $this->option('tier') ?: $this->choice('License tier', ['standard', 'extended', 'unlimited'], 0);
        $customer = $this->option('customer') ?: $this->ask('Customer name', '');
        $email = $this->option('email') ?: $this->ask('Customer email', '');
        $expires = $this->option('expires') ?: $this->ask('Expires (YYYY-MM-DD or "lifetime")', 'lifetime');
        $maxUsers = (int) $this->option('max-users');
        $plugins = $this->option('plugins') ?: 'all';

        $key = LicenseService::generate([
            'domain' => $domain,
            'tier' => $tier,
            'customer' => $customer,
            'email' => $email,
            'expires' => $expires,
            'max_users' => $maxUsers,
            'plugins' => $plugins,
        ]);

        $this->newLine();
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║     LaravelCP License Key Generated      ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        $this->line("  <fg=green>License Key:</>");
        $this->newLine();
        $this->line("  <fg=yellow>{$key}</>");
        $this->newLine();

        $this->table(
            ['Property', 'Value'],
            [
                ['Domain', $domain],
                ['Tier', ucfirst($tier)],
                ['Customer', $customer ?: '-'],
                ['Email', $email ?: '-'],
                ['Expires', $expires],
                ['Max Users', $maxUsers === 0 ? 'Unlimited' : $maxUsers],
                ['Plugins', $plugins],
            ]
        );

        $this->newLine();
        $this->warn('⚠  Keep this key secure. Share only with the customer.');

        return Command::SUCCESS;
    }
}
