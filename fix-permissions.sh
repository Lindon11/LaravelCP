#!/bin/bash

# Fix Laravel 403 Forbidden Error
# Run this on your live server via SSH

echo "🔧 Fixing Laravel permissions and configuration..."

cd ~/new.criminal-empire.co.uk/laravel-api || exit

# Fix ownership (replace 'username' with your actual username)
echo "👤 Fixing ownership..."
# Uncomment and update the line below with your actual username
# chown -R username:username .

# Fix directory permissions (755 = readable/executable by everyone, writable by owner)
echo "📁 Setting directory permissions..."
find . -type d -exec chmod 755 {} \;

# Fix file permissions (644 = readable by everyone, writable by owner)
echo "📄 Setting file permissions..."
find . -type f -exec chmod 644 {} \;

# Make storage and cache writable (775 = group writable too)
echo "💾 Setting storage permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Make artisan executable
chmod +x artisan

# Ensure .htaccess is readable
echo "🔐 Ensuring .htaccess is accessible..."
chmod 644 public/.htaccess

# Clear and recache everything
echo "🗑️ Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "✅ Permissions fixed!"
echo ""
echo "If still getting 403, check with your hosting provider:"
echo "1. PHP version is 8.2+"
echo "2. mod_rewrite is enabled"
echo "3. AllowOverride is set to All for your directory"
