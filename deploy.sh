#!/bin/bash

# Laravel Production Deployment Script for new.criminal-empire.co.uk
# Run this script AFTER uploading files via FTP/SFTP

echo "🚀 Starting Laravel deployment..."

# Navigate to Laravel directory
cd ~/new.criminal-empire.co.uk/laravel-api || exit

# Install Composer dependencies (production only, no dev packages)
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# Copy production environment file
echo "⚙️  Setting up environment..."
cp .env.production .env

# Generate application key if not set
php artisan key:generate --force

# Clear and cache configuration
echo "🗑️  Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "💾 Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Create storage link
echo "🔗 Creating storage symlink..."
php artisan storage:link

echo "✅ Deployment complete!"
echo ""
echo "⚠️  IMPORTANT: Verify these settings in your .env file:"
echo "   - DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
echo "   - APP_URL (should be https://new.criminal-empire.co.uk)"
echo "   - APP_DEBUG=false"
echo ""
echo "🌐 Your site should now be live at: https://new.criminal-empire.co.uk"
