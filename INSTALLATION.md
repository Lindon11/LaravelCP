# LaravelCP Installation Guide

## Requirements

- **PHP 8.3+** with extensions: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- **MySQL 8.0+** or MariaDB 10.6+
- **Composer 2.x**
- **Node.js 18+** and npm (for admin panel)
- **Redis** (optional, recommended for caching)

## Architecture Overview

LaravelCP uses a modular architecture:

```
app/
├── Core/                    # Essential system (always loaded)
│   ├── Http/Controllers/    # API & Admin controllers
│   ├── Models/              # User, Settings, etc.
│   ├── Providers/           # Service providers
│   └── Services/            # Core business logic
├── Plugins/                 # Game features (28 built-in)
│   ├── Bank/
│   ├── Combat/
│   ├── Crimes/
│   └── ...
└── Facades/                 # Hook system facade
```

---

## Installation Methods

### Method 1: Docker (Recommended)

```bash
# 1. Clone repository
git clone https://github.com/YourUsername/LaravelCP.git
cd LaravelCP

# 2. Start Docker containers
docker compose up -d

# 3. Install PHP dependencies
docker compose exec app composer install

# 4. Configure environment
cp .env.example .env
docker compose exec app php artisan key:generate

# 5. Edit .env (update if needed)
# DB_HOST=mysql
# DB_DATABASE=laravel
# DB_USERNAME=laravel
# DB_PASSWORD=secret

# 6. Run migrations
docker compose exec app php artisan migrate

# 7. Seed database (includes default admin)
docker compose exec app php artisan db:seed

# 8. Build admin panel
docker compose exec app bash -c "cd resources/admin && npm install && npm run build"

# 9. Access application
# API: http://localhost:8001
# Admin: http://localhost:8001/admin
```

**Default Admin Credentials:**
- Username: `admin`
- Password: `admin123`
- ⚠️ You will be forced to change password on first login

---

### Method 2: Manual Installation

```bash
# 1. Clone repository
git clone https://github.com/YourUsername/LaravelCP.git
cd LaravelCP

# 2. Install PHP dependencies
composer install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Edit .env with your database credentials
nano .env

# 5. Create database
mysql -u root -p -e "CREATE DATABASE laravelcp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Run migrations
php artisan migrate

# 7. Seed database
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
php artisan serve --port=8001
```

---

### Method 3: Web Installer

1. Complete steps 1-5 from Manual Installation
2. Navigate to `http://localhost:8001/install`
3. Follow the on-screen wizard:
   - Database configuration
   - Admin account creation
   - Initial settings

---

## Post-Installation

### Generate API Documentation

```bash
php artisan scribe:generate
# View at /docs
```

### Clear Caches (if issues occur)

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
composer dump-autoload
```

### Verify Installation

```bash
# Check routes
php artisan route:list | head -20

# Check plugins discovered
php artisan tinker --execute="echo count(app(App\Core\Services\PluginManagerService::class)->getAllPlugins()) . ' plugins found';"
```

---

## Configuration

### Environment Variables

Key `.env` settings:

```env
# Application
APP_NAME=LaravelCP
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravelcp
DB_USERNAME=your_user
DB_PASSWORD=your_password

# Cache (recommended)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Plugin Configuration

Edit `config/plugins.php`:

```php
return [
    'path' => app_path('Plugins'),
    'cache' => env('PLUGINS_CACHE', true),
    'auto_discover' => true,
];
```

---

## Production Deployment

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/laravelcp/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Optimization

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### Cron Job (for scheduled tasks)

```bash
* * * * * cd /var/www/laravelcp && php artisan schedule:run >> /dev/null 2>&1
```

---

## Troubleshooting

### Common Issues

**1. 500 Server Error**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Fix permissions
chmod -R 775 storage bootstrap/cache
```

**2. Database Connection Failed**
- Verify `.env` database credentials
- Ensure MySQL is running
- Check database exists

**3. Routes Not Found**
```bash
php artisan route:clear
php artisan config:clear
composer dump-autoload
```

**4. Plugins Not Loading**
```bash
# Check plugin discovery
php artisan tinker --execute="dd(app(App\Core\Services\PluginManagerService::class)->getAllPlugins());"
```

---

## Updating

```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer install
npm install

# Run new migrations
php artisan migrate

# Clear caches
php artisan optimize:clear

# Rebuild admin panel
cd resources/admin && npm run build
```

---

## Support

- 📖 API Documentation: `/docs`
- 🐛 Issues: GitHub Issues
- 📧 Contact: your@email.com
