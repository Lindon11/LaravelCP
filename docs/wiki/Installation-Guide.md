# Installation Guide

Complete guide to installing LaravelCP on your server or local development environment.

---

## Requirements

### Server Requirements

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| **PHP** | 8.3+ | 8.3+ |
| **MySQL** | 8.0+ | 8.0+ |
| **Node.js** | 18+ | 20+ |
| **Composer** | 2.x | 2.x |
| **Redis** | Optional | 6.x+ |

### PHP Extensions Required

```
BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, 
OpenSSL, PCRE, PDO, PDO_MySQL, Tokenizer, XML, Zip
```

---

## Installation Methods

### Method 1: Docker (Recommended)

Docker provides the easiest setup with all dependencies pre-configured.

```bash
# 1. Clone repository
git clone https://github.com/Lindon11/LaravelCP.git
cd LaravelCP

# 2. Start Docker containers
docker compose up -d

# 3. Install PHP dependencies
docker compose exec app composer install

# 4. Configure environment
cp .env.example .env
docker compose exec app php artisan key:generate

# 5. Configure database in .env (already set for Docker)
# DB_HOST=mysql
# DB_DATABASE=laravel
# DB_USERNAME=laravel
# DB_PASSWORD=secret

# 6. Run migrations and seed database
docker compose exec app php artisan migrate --seed

# 7. Build admin panel
docker compose exec app bash -c "cd resources/admin && npm install && npm run build"

# 8. Access your application
# API: http://localhost:8001
# Admin Panel: http://localhost:8001/admin
```

**Default Login:**
- Username: `admin`
- Password: `admin123`

---

### Method 2: Manual Installation

For traditional server setups without Docker.

```bash
# 1. Clone repository
git clone https://github.com/Lindon11/LaravelCP.git
cd LaravelCP

# 2. Install PHP dependencies
composer install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Create database
mysql -u root -p -e "CREATE DATABASE laravelcp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Edit .env with your database credentials
nano .env
```

**Edit `.env` file:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravelcp
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

```bash
# 6. Run migrations
php artisan migrate

# 7. Seed database with default data
php artisan db:seed

# 8. Build admin panel
cd resources/admin
npm install
npm run build
cd ../..

# 9. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 10. Start development server
php artisan serve --host=0.0.0.0 --port=8001
```

---

### Method 3: Web Installer

1. Complete steps 1-5 from Manual Installation
2. Navigate to `http://localhost:8001/install`
3. Follow the wizard:
   - System requirements check
   - Database configuration
   - Admin account creation
   - Initial settings

---

## Post-Installation Steps

### 1. Verify Installation

```bash
# Check all routes are registered
php artisan route:list | head -30

# Verify plugins are discovered
php artisan tinker --execute="echo count(app('App\Core\Services\PluginManagerService')->getAllPlugins()) . ' plugins found';"
```

### 2. Generate API Documentation

```bash
php artisan scribe:generate
# View at http://localhost:8001/docs
```

### 3. Configure Cron (Production)

Add to your server's crontab:

```bash
* * * * * cd /path/to/laravelcp && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Set Up Queue Worker (Optional but Recommended)

```bash
# For production, use Supervisor
php artisan queue:work --daemon
```

---

## Directory Permissions

Ensure these directories are writable:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod -R 775 public/admin
```

---

## Common Installation Issues

### "SQLSTATE[HY000] Access denied"

Check your `.env` database credentials:
```bash
php artisan config:clear
php artisan cache:clear
```

### "Class not found" errors

Regenerate autoload files:
```bash
composer dump-autoload
php artisan clear-compiled
```

### Admin panel shows blank page

Rebuild the admin panel:
```bash
cd resources/admin
rm -rf node_modules dist
npm install
npm run build
```

### Permissions errors

```bash
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Next Steps

After installation:

1. **[Configure your environment](Configuration)** - Set up email, cache, etc.
2. **[Explore the Admin Panel](Admin-Dashboard)** - Manage your game
3. **[Create your first plugin](Creating-Plugins)** - Extend functionality
4. **[Set up webhooks](Webhooks)** - Integrate with Discord/Slack

---

## Getting Help

- Check [Troubleshooting](Troubleshooting) for common issues
- Open an issue on [GitHub](https://github.com/Lindon11/LaravelCP/issues)
