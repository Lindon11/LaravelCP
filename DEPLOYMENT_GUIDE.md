# 🚀 Live Server Deployment Guide

## Hosting Panel Configuration

### Domain Settings
- **Domain name**: `new` (becomes new.criminal-empire.co.uk)
- **Hosting type**: Website
- **Document root**: `new.criminal-empire.co.uk/laravel-api/public` ⚠️ **CRITICAL**

### SSL/TLS Support
- ✅ Enable SSL/TLS
- ✅ Redirect HTTP to HTTPS (SEO friendly 301)

### Web Scripting
Ensure PHP 8.2+ is enabled

## Pre-Deployment Checklist

### 1. Database Setup (via hosting panel)
- Create MySQL database
- Create database user with all privileges
- Note down: DB name, username, password

### 2. File Upload
Upload entire project to: `~/new.criminal-empire.co.uk/`

**Files to upload:**
```
new.criminal-empire.co.uk/
├── laravel-api/          # Your Laravel app
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/          # ⚠️ This is your document root
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/          # May need to run composer install on server
│   ├── .env.production  # Rename to .env after upload
│   ├── artisan
│   └── composer.json
└── (other files)
```

### 3. SSH Access Required
You'll need SSH access to run deployment commands.

## Deployment Steps

### Step 1: Upload Files
Use FTP/SFTP to upload all files to the server.

### Step 2: Connect via SSH
```bash
ssh your-username@new.criminal-empire.co.uk
```

### Step 3: Navigate to Project
```bash
cd ~/new.criminal-empire.co.uk
```

### Step 4: Update .env File
```bash
cd laravel-api
cp .env.production .env
nano .env  # Edit with your actual database credentials
```

**Update these values:**
```env
DB_DATABASE=your_actual_database_name
DB_USERNAME=your_actual_db_user
DB_PASSWORD=your_actual_db_password
```

### Step 5: Run Deployment Script
```bash
chmod +x ../deploy.sh
../deploy.sh
```

Or manually run:
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Link storage
php artisan storage:link
```

## Post-Deployment Verification

### 1. Test the Site
Visit: https://new.criminal-empire.co.uk

### 2. Check Error Logs
```bash
tail -f ~/new.criminal-empire.co.uk/laravel-api/storage/logs/laravel.log
```

### 3. Test Database Connection
```bash
cd ~/new.criminal-empire.co.uk/laravel-api
php artisan tinker
# Then run: DB::connection()->getPdo();
```

## Important Security Notes

### ✅ Production Settings in .env
```env
APP_ENV=production
APP_DEBUG=false           # NEVER true in production
SESSION_SECURE_COOKIE=true
```

### ✅ File Permissions
```bash
# Application files
find ~/new.criminal-empire.co.uk/laravel-api -type f -exec chmod 644 {} \;
find ~/new.criminal-empire.co.uk/laravel-api -type d -exec chmod 755 {} \;

# Writable directories
chmod -R 775 ~/new.criminal-empire.co.uk/laravel-api/storage
chmod -R 775 ~/new.criminal-empire.co.uk/laravel-api/bootstrap/cache
```

### ⚠️ Never Upload These Files
- `.env` (create on server from .env.production)
- `node_modules/` (rebuild on server if needed)
- `.git/` (optional, but be careful with sensitive data)
- `storage/logs/*` (will be regenerated)

## Troubleshooting

### "500 Internal Server Error"
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check PHP error logs (location varies by hosting)
tail -f ~/logs/error_log
```

### Permission Issues
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache  # or your web server user
```

### Database Connection Failed
- Verify credentials in `.env`
- Check if database exists
- Ensure database user has correct privileges
- Try `DB_HOST=localhost` or `DB_HOST=127.0.0.1`

### Composer Not Found
```bash
# Most hosting panels have composer available
which composer

# Or install in your home directory
curl -sS https://getcomposer.org/installer | php
mv composer.phar ~/bin/composer
```

## Quick Reference

**Document Root (in hosting panel):**
```
new.criminal-empire.co.uk/laravel-api/public
```

**Update Production Code:**
```bash
cd ~/new.criminal-empire.co.uk/laravel-api
git pull origin main  # if using git
composer install --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Clear All Caches:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```
