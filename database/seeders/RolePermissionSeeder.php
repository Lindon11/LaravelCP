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

        // Create permissions (using consistent naming with spaces)
        $permissions = [
            // User Management
            'manage users',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'ban users',
            
            // Module Management
            'manage modules',
            'toggle modules',
            
            // Game Management
            'manage crimes',
            'manage gangs',
            'manage properties',
            'manage locations',
            'manage items',
            'manage drugs',
            'manage ranks',
            'manage organized crimes',
            
            // Forum Management
            'manage forum',
            'moderate forum',
            'lock topics',
            'delete posts',
            
            // Chat Management
            'moderate chat',
            'manage chat channels',
            
            // Support
            'manage tickets',
            'view reports',
            
            // System
            'view admin panel',
            'view logs',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles: admin, moderator, user
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $moderator = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Admin gets ALL permissions
        $admin->syncPermissions(Permission::all());

        // Moderator gets limited permissions
        $moderator->syncPermissions([
            'view admin panel',
            'view users',
            'ban users',
            'moderate forum',
            'lock topics',
            'delete posts',
            'moderate chat',
            'manage tickets',
            'view reports',
        ]);

        // User role gets no admin permissions (they just play the game)
        $user->syncPermissions([]);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('- admin: Full access');
        $this->command->info('- moderator: Forum/chat moderation, user bans, tickets');
        $this->command->info('- user: Regular player (no admin access)');
    }
}
