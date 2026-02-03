<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This creates a default admin user for quick deployment.
     * 
     * ⚠️ SECURITY WARNING:
     * Only use this in development or immediately after deployment.
     * Change the password immediately after first login.
     *
     * Default Credentials:
     * Username: admin
     * Password: admin123
     */
    public function run(): void
    {
        // Check if admin user already exists
        if (User::where('username', 'admin')->exists()) {
            $this->command->warn('Admin user already exists. Skipping...');
            return;
        }

        // Check if admin role exists
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $this->command->error('Admin role not found. Run RolePermissionSeeder first.');
            return;
        }

        $this->command->info('Creating default admin user...');

        $user = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
            'rank_id' => 1,
            'rank' => 'Thug',
            'location' => 'Detroit',
            'location_id' => 1,
            'force_password_change' => true, // Force password change on first login
        ]);

        // Assign admin role
        $user->assignRole('admin');

        $this->command->info('✅ Default admin user created successfully!');
        $this->command->newLine();
        $this->command->warn('⚠️  DEFAULT CREDENTIALS:');
        $this->command->line('   Username: admin');
        $this->command->line('   Password: admin123');
        $this->command->newLine();
        $this->command->error('🔒 CHANGE PASSWORD IMMEDIATELY AFTER FIRST LOGIN!');
    }
}
