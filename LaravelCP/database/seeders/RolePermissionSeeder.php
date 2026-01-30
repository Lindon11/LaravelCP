<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'manage users',
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Player Management
            'manage players',
            'view players',
            'edit players',
            'ban players',
            
            // Module Management
            'manage modules',
            'toggle modules',
            
            // Game Management
            'manage crimes',
            'manage gangs',
            'manage properties',
            'manage locations',
            'manage organized crimes',
            
            // Forum Management
            'manage forum',
            'moderate forum',
            'lock topics',
            'delete posts',
            
            // System
            'view admin panel',
            'view logs',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'view admin panel',
            'manage players',
            'view players',
            'edit players',
            'manage crimes',
            'manage gangs',
            'manage properties',
            'manage locations',
            'manage organized crimes',
            'moderate forum',
            'lock topics',
            'delete posts',
            'view logs',
        ]);

        $moderator = Role::firstOrCreate(['name' => 'moderator']);
        $moderator->givePermissionTo([
            'view admin panel',
            'view players',
            'moderate forum',
            'lock topics',
            'delete posts',
        ]);

        $player = Role::firstOrCreate(['name' => 'player']);
        // Players get no special permissions - they use the game normally

        // Assign super_admin role to first user (admin@gangster-legends.com)
        $adminUser = \App\Models\User::where('email', 'admin@gangster-legends.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('super_admin');
        }
    }
}
