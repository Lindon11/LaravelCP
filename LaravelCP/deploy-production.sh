#!/bin/bash

# Git Deployment Script for Production Server
# Run this on your server: /var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/

echo "🚀 Starting Git deployment..."

# Navigate to project directory
cd /var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/ || exit

# Stash any local changes (like .env)
echo "💾 Stashing local changes..."
git stash

# Pull latest changes from main branch
echo "📥 Pulling latest code from Git..."
git pull origin main

# Restore local changes
git stash pop || echo "No stashed changes to restore"

# Install/update Composer dependencies (production only)
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Fix ownership and permissions
echo "🔐 Fixing permissions..."
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 775 storage bootstrap/cache
chmod +x artisan

# Clear all caches
echo "🗑️  Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo "⚡ Caching config and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# Restart PHP-FPM (if you have access)
echo "🔄 Restarting services..."
systemctl reload php-fpm || echo "⚠️  Could not reload PHP-FPM (may need sudo)"

echo "✅ Deployment complete!"
echo "🌐 Visit: https://new.criminal-empire.co.uk"
