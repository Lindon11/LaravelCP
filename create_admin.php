<?php
/**
 * CLI Admin User Creator
 *
 * Usage: php create_admin.php
 *
 * This script creates an admin user interactively.
 * For production, use the web installer at /install instead.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "╔══════════════════════════════════════╗\n";
echo "║     LaravelCP Admin User Creator     ║\n";
echo "╚══════════════════════════════════════╝\n\n";

// Get username
echo "Enter username: ";
$username = trim(fgets(STDIN));
if (empty($username)) {
    echo "❌ Username cannot be empty\n";
    exit(1);
}

// Check if username exists
if (User::where('username', $username)->exists()) {
    echo "❌ Username already exists\n";
    exit(1);
}

// Get email
echo "Enter email: ";
$email = trim(fgets(STDIN));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "❌ Invalid email address\n";
    exit(1);
}

// Check if email exists
if (User::where('email', $email)->exists()) {
    echo "❌ Email already exists\n";
    exit(1);
}

// Get password
echo "Enter password (min 8 chars): ";
system('stty -echo'); // Hide password input
$password = trim(fgets(STDIN));
system('stty echo');
echo "\n";

if (strlen($password) < 8) {
    echo "❌ Password must be at least 8 characters\n";
    exit(1);
}

// Confirm password
echo "Confirm password: ";
system('stty -echo');
$passwordConfirm = trim(fgets(STDIN));
system('stty echo');
echo "\n";

if ($password !== $passwordConfirm) {
    echo "❌ Passwords do not match\n";
    exit(1);
}

// Check if migrations have been run
echo "\nChecking database setup...\n";
try {
    \DB::table('users')->count();
} catch (\Exception $e) {
    echo "❌ Database tables not found. Please run migrations first:\n";
    echo "   php artisan migrate\n";
    exit(1);
}

// Check if roles are seeded
$adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
if (!$adminRole) {
    echo "❌ Admin role not found. Please run seeders first:\n";
    echo "   php artisan db:seed\n";
    echo "   OR\n";
    echo "   php artisan db:seed --class=RolePermissionSeeder\n";
    exit(1);
}

echo "✓ Database setup verified\n";

// Create admin user
echo "\nCreating admin user...\n";
try {
    $user = User::create([
        'name' => $username,
        'username' => $username,
        'email' => $email,
        'password' => Hash::make($password),
        'email_verified_at' => now(),
        'rank_id' => 1,
        'rank' => 'Thug',
        'location' => 'Detroit',
        'location_id' => 1,
    ]);

    // Assign admin role
    $user->assignRole('admin');

    // Verify assignment
    if (!$user->hasRole('admin')) {
        throw new \Exception('Failed to assign admin role');
    }

    echo "\n✅ Admin user created successfully!\n\n";
    echo "Username: {$username}\n";
    echo "Email: {$email}\n";
    echo "Roles: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";
    echo "\nYou can now login at your site's /login page.\n";
} catch (\Exception $e) {
    echo "❌ Error creating user: " . $e->getMessage() . "\n";
    exit(1);
}
