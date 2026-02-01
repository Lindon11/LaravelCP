<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create admin user
$user = User::create([
    'username' => 'admin',
    'email' => 'admin@laravelcp.local',
    'password' => Hash::make('admin123'),
]);

// Assign admin role
$user->assignRole('admin');

echo "✅ Admin user created successfully!\n\n";
echo "Username: admin\n";
echo "Password: admin123\n";
echo "Email: admin@laravelcp.local\n";
