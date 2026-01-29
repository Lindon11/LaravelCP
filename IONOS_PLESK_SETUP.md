# 🔧 IONOS Plesk Setup Guide for new.criminal-empire.co.uk

## Step 1: Check Current Document Root

### SSH into your server
```bash
ssh your-username@criminal-empire.co.uk
# Or use the SSH details from IONOS panel
```

### Find your subdomain's current document root
```bash
# Check Plesk's virtual host configuration
grep -r "new.criminal-empire.co.uk" /var/www/vhosts/criminal-empire.co.uk/conf/

# Or check directly in Plesk database
mysql -u admin -p$(cat /etc/psa/.psa.shadow) psa -e "SELECT name, www_root FROM hosting WHERE name='new.criminal-empire.co.uk';"
```

### List your domain structure
```bash
ls -la /var/www/vhosts/criminal-empire.co.uk/
```

## Step 2: Typical Plesk Structure

IONOS/Plesk usually organizes files like this:
```
/var/www/vhosts/criminal-empire.co.uk/
├── criminal-empire.co.uk/          # Main domain
│   └── httpdocs/                   # Main domain document root
├── new.criminal-empire.co.uk/      # Your subdomain
│   └── httpdocs/                   # Subdomain document root (THIS is where you upload)
├── error_docs/
├── logs/
└── private/
```

## Step 3: Upload Your Laravel Files

### Your files should go here:
```
/var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/httpdocs/
```

### The structure should be:
```
httpdocs/
└── laravel-api/
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/        # Laravel public directory
    │   ├── index.php
    │   └── .htaccess
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    ├── .env
    ├── artisan
    └── composer.json
```

## Step 4: Set Document Root in Plesk

### Option A: Via Plesk Panel (Recommended)
1. Login to Plesk
2. Go to **Websites & Domains**
3. Click on **new.criminal-empire.co.uk**
4. Click **Hosting Settings**
5. Set **Document Root** to:
   ```
   httpdocs/laravel-api/public
   ```
6. Click **OK**

### Option B: Via SSH (Advanced)
```bash
# Navigate to your subdomain directory
cd /var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/

# Check current document root
ls -la httpdocs/

# You should see your laravel-api folder
ls -la httpdocs/laravel-api/

# Verify public folder exists
ls -la httpdocs/laravel-api/public/
```

## Step 5: Fix Permissions (IMPORTANT)

### Set correct ownership
```bash
cd /var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/httpdocs/laravel-api

# Fix ownership (Plesk uses specific user per domain)
# Find your domain user:
ls -l ../ | grep laravel-api

# The owner should be something like: username or domain-user
# Set ownership:
chown -R new_criminal-empire_co_uk:psacln .

# If above doesn't work, try:
chown -R $(stat -c '%U:%G' ../.) .
```

### Set correct permissions
```bash
# Fix all directory permissions
find . -type d -exec chmod 755 {} \;

# Fix all file permissions  
find . -type f -exec chmod 644 {} \;

# Make storage and cache writable
chmod -R 775 storage bootstrap/cache

# Make artisan executable
chmod +x artisan
```

## Step 6: Setup Laravel

### Install Composer dependencies
```bash
cd /var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/httpdocs/laravel-api

# Check if composer is available
which composer

# If composer exists, install dependencies
composer install --no-dev --optimize-autoloader

# If composer not found, use Plesk's composer
/opt/plesk/php/8.2/bin/php /usr/lib64/plesk-9.0/composer.phar install --no-dev --optimize-autoloader
```

### Setup .env file
```bash
# Copy production environment
cp .env.production .env

# Edit with nano or vi
nano .env
```

### Configure database credentials in .env:
```env
DB_HOST=localhost
DB_DATABASE=your_plesk_database_name
DB_USERNAME=your_plesk_db_user
DB_PASSWORD=your_plesk_db_password
```

### Run Laravel setup
```bash
# Generate key if needed
php artisan key:generate

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Create storage symlink
php artisan storage:link
```

## Step 7: Quick Check Commands

### Check PHP version
```bash
php -v
# Should show PHP 8.2 or higher
```

### Check Apache modules (for mod_rewrite)
```bash
apachectl -M | grep rewrite
# Should show: rewrite_module (shared)
```

### Check if site is accessible
```bash
curl -I https://new.criminal-empire.co.uk
```

### Check Laravel logs for errors
```bash
tail -f /var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/httpdocs/laravel-api/storage/logs/laravel.log
```

### Check Apache error logs
```bash
tail -f /var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/logs/error_log
```

## Step 8: Troubleshooting 403 Errors

### If you still get 403, check these:

```bash
# 1. Verify document root in Plesk vhost config
cat /var/www/vhosts/criminal-empire.co.uk/conf/vhost.conf | grep -A5 "new.criminal-empire.co.uk"

# 2. Check .htaccess exists and is readable
ls -la /var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/httpdocs/laravel-api/public/.htaccess

# 3. Check index.php exists and is readable  
ls -la /var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/httpdocs/laravel-api/public/index.php

# 4. Test with a simple PHP file
echo "<?php phpinfo();" > /var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/httpdocs/laravel-api/public/test.php
# Then visit: https://new.criminal-empire.co.uk/test.php
# If this works, problem is Laravel-specific
# DELETE test.php after!
```

## Quick Reference Card

### Your paths:
- **SSH User**: Check IONOS panel for username
- **Domain base**: `/var/www/vhosts/criminal-empire.co.uk/`
- **Subdomain folder**: `/var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/`
- **Upload location**: `/var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/httpdocs/`
- **Document root (set in Plesk)**: `httpdocs/laravel-api/public`

### One-command permission fix:
```bash
cd /var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/httpdocs/laravel-api && \
find . -type d -exec chmod 755 {} \; && \
find . -type f -exec chmod 644 {} \; && \
chmod -R 775 storage bootstrap/cache && \
chmod +x artisan && \
echo "Permissions fixed!"
```

### One-command Laravel setup:
```bash
cd /var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/httpdocs/laravel-api && \
composer install --no-dev --optimize-autoloader && \
cp .env.production .env && \
php artisan key:generate && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
php artisan storage:link && \
echo "Laravel ready!"
```

## Need Help?

If you get stuck, run this diagnostic command:
```bash
cd /var/www/vhosts/criminal-empire.co.uk/new.criminal-empire.co.uk/httpdocs/ && \
echo "=== Directory Structure ===" && \
ls -la && \
echo "" && \
echo "=== Laravel Public Directory ===" && \
ls -la laravel-api/public/ && \
echo "" && \
echo "=== PHP Version ===" && \
php -v && \
echo "" && \
echo "=== Permissions ===" && \
ls -ld laravel-api/storage laravel-api/bootstrap/cache
```

Copy the output and share it if you need further help!
