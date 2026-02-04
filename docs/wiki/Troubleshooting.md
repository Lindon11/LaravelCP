# Troubleshooting

Common issues and their solutions when working with LaravelCP.

---

## Installation Issues

### "SQLSTATE[HY000] Access denied for user"

**Problem:** Database connection failed.

**Solutions:**
1. Verify `.env` credentials match your database:
```env
DB_HOST=127.0.0.1
DB_DATABASE=laravelcp
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

2. For Docker, use the service name:
```env
DB_HOST=mysql
```

3. Clear config cache:
```bash
php artisan config:clear
```

---

### "Class not found" Errors

**Problem:** Autoloader hasn't registered new classes.

**Solutions:**
```bash
composer dump-autoload
php artisan clear-compiled
php artisan cache:clear
```

---

### "The page isn't working" / 500 Error

**Problem:** Application error.

**Solutions:**

1. Check Laravel log:
```bash
tail -f storage/logs/laravel.log
```

2. Enable debug mode temporarily:
```env
APP_DEBUG=true
```

3. Fix permissions:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

### Admin Panel Shows Blank Page

**Problem:** Vue.js build failed or missing.

**Solutions:**

1. Rebuild admin panel:
```bash
cd resources/admin
rm -rf node_modules dist
npm install
npm run build
```

2. Check for build errors in console.

3. Verify files exist:
```bash
ls -la public/admin/assets/
```

---

### Routes Not Found (404)

**Problem:** Routes not registered.

**Solutions:**
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

Verify routes:
```bash
php artisan route:list | grep your-route
```

---

### Plugins Not Loading

**Problem:** Plugin discovery failed.

**Solutions:**

1. Check plugin structure:
```bash
ls -la app/Plugins/YourPlugin/
# Should have: plugin.json, Controllers/, Models/, etc.
```

2. Verify `plugin.json` is valid JSON:
```bash
cat app/Plugins/YourPlugin/plugin.json | python -m json.tool
```

3. Check discovery:
```bash
php artisan tinker --execute="dd(app('App\Core\Services\PluginManagerService')->getAllPlugins());"
```

4. Clear plugin cache:
```bash
php artisan cache:clear
```

---

## Runtime Issues

### "Token Mismatch" / CSRF Errors

**Problem:** Session/cookie issues.

**Solutions:**

1. Clear session:
```bash
php artisan session:clear
```

2. Check session configuration:
```env
SESSION_DRIVER=file
SESSION_DOMAIN=localhost
```

3. For API, ensure you're using Sanctum tokens, not sessions.

---

### "Unauthenticated" on All API Requests

**Problem:** Auth token invalid or missing.

**Solutions:**

1. Check token format:
```
Authorization: Bearer YOUR_TOKEN
```

2. Verify Sanctum configuration:
```php
// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', '')),
```

3. Check `.env`:
```env
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

---

### Slow Page Loads

**Problem:** Performance issues.

**Solutions:**

1. Enable caching:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

2. Use Redis for cache/sessions:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

3. Optimize autoloader:
```bash
composer install --optimize-autoloader --no-dev
```

---

### Queue Jobs Not Processing

**Problem:** Queue worker not running.

**Solutions:**

1. Start queue worker:
```bash
php artisan queue:work
```

2. Check failed jobs:
```bash
php artisan queue:failed
```

3. Retry failed jobs:
```bash
php artisan queue:retry all
```

4. For sync processing (development):
```env
QUEUE_CONNECTION=sync
```

---

### Email Not Sending

**Problem:** Mail configuration issues.

**Solutions:**

1. Verify `.env` settings:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

2. Test mail:
```bash
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com'));
```

3. Check mail log:
```bash
tail -f storage/logs/laravel.log | grep -i mail
```

---

## Database Issues

### Migration Errors

**Problem:** Migration failed.

**Solutions:**

1. Check migration status:
```bash
php artisan migrate:status
```

2. Rollback and retry:
```bash
php artisan migrate:rollback
php artisan migrate
```

3. Fresh install (⚠️ destroys data):
```bash
php artisan migrate:fresh --seed
```

---

### "Table already exists"

**Problem:** Trying to create existing table.

**Solutions:**

1. Skip existing:
```bash
php artisan migrate --force
```

2. Mark as run without executing:
```bash
php artisan migrate:status
# Note the migration name
# Manually add to migrations table if needed
```

---

### Seeder Errors

**Problem:** Seeder failed.

**Solutions:**

1. Run specific seeder:
```bash
php artisan db:seed --class=SpecificSeeder
```

2. Check seeder file for errors:
```bash
php -l database/seeders/YourSeeder.php
```

---

## Docker Issues

### Container Won't Start

**Problem:** Docker compose errors.

**Solutions:**

1. Check logs:
```bash
docker-compose logs app
docker-compose logs mysql
```

2. Rebuild containers:
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

3. Check ports:
```bash
docker-compose ps
# Ensure no port conflicts
```

---

### "Connection refused" to MySQL

**Problem:** Can't connect to Docker MySQL.

**Solutions:**

1. Use service name, not localhost:
```env
DB_HOST=mysql  # Not 127.0.0.1
```

2. Wait for MySQL to be ready:
```bash
docker-compose exec app php artisan migrate
# May need to wait a minute after starting containers
```

---

### Files Not Syncing

**Problem:** Code changes not reflected.

**Solutions:**

1. Check volume mounts in `docker-compose.yml`

2. Restart containers:
```bash
docker-compose restart app
```

---

## Admin Panel Issues

### Login Redirect Loop

**Problem:** Can't stay logged in.

**Solutions:**

1. Clear browser cookies
2. Check CORS settings:
```env
CORS_ALLOWED_ORIGINS=http://localhost:5173
```

3. Verify session domain:
```env
SESSION_DOMAIN=localhost
```

---

### API Calls Return HTML

**Problem:** Getting error page instead of JSON.

**Solutions:**

1. Add proper headers:
```
Accept: application/json
Content-Type: application/json
```

2. Check route exists:
```bash
php artisan route:list | grep your-endpoint
```

---

## Performance Tips

### Optimize for Production

```bash
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Use Redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Enable OPcache

Add to `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
```

---

## Getting Help

### Collecting Debug Info

Before asking for help, gather:

```bash
# PHP version
php -v

# Laravel version
php artisan --version

# List of errors
tail -100 storage/logs/laravel.log

# Environment info
php artisan about
```

### Where to Get Help

1. Check this documentation
2. Search [GitHub Issues](https://github.com/Lindon11/LaravelCP/issues)
3. Open a new issue with debug info

---

## Quick Reference

### Clear All Caches

```bash
php artisan optimize:clear
```

### Reset Everything

```bash
php artisan migrate:fresh --seed
php artisan optimize:clear
cd resources/admin && npm run build
```

### Check System Status

```bash
php artisan about
php artisan route:list | head -20
php artisan queue:failed
tail -20 storage/logs/laravel.log
```
