<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $player = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Create permissions
        $permissions = [
            'manage-system',
            'manage-users',
            'manage-content',
            'view-admin',
            'moderate-chat',
            'moderate-forum',
            'view-reports',
            'manage-tickets',
            'manage-bans',
            'manage-gangs',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Assign permissions to admin
        $admin->givePermissionTo(Permission::all());

        // Assign permissions to staff
        $staff->givePermissionTo([
            'view-admin',
            'moderate-chat',
            'moderate-forum',
            'view-reports',
            'manage-tickets',
            'manage-bans',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
