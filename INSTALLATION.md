# LaravelCP Installation Guide

## Prerequisites

- PHP 8.2 or higher
- MySQL 8.0 or higher
- Composer
- Node.js 18+ and NPM

## Installation Methods

### Method 1: Web Installer (Recommended)

**IMPORTANT:** The web installer steps must be followed in this exact order:

1. **Navigate to `/install`** in your browser
2. **Requirements Check** - Verify server requirements
3. **Database Configuration** - Enter database credentials
4. **Application Settings** - Configure app name and URL
5. **Run Installation** - This creates database tables and seeds data (including roles)
6. **Create Admin User** - Create your admin account (roles now exist)
7. **Complete** - Finalize installation

**Note:** Do NOT skip to admin creation step. The database must be migrated and seeded first, or admin role assignment will fail.

### Method 2: Command Line Installation

This is the most reliable method:

```bash
# 1. Clone repository
git clone https://github.com/Lindon11/LaravelCP.git
cd LaravelCP

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Copy environment file
cp .env.example .env

# 5. Edit .env file with your database credentials
nano .env
# Set:
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=your_database
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# 6. Generate application key
php artisan key:generate

# 7. Run database migrations (creates tables)
php artisan migrate

# 8. Seed database (creates roles, permissions, and game data)
php artisan db:seed

# 9. Build frontend assets
npm run build

# 10. Build admin panel
cd resources/admin
npm install
npm run build
cd ../..

# 11. Create admin user (NOW roles exist)
php create_admin.php
# OR use artisan tinker:
php artisan tinker
>>> $user = User::create(['username'=>'admin','name'=>'admin','email'=>'admin@example.com','password'=>bcrypt('yourpassword'),'email_verified_at'=>now(),'rank_id'=>1,'rank'=>'Thug','location'=>'Detroit','location_id'=>1]);
>>> $user->assignRole('admin');
>>> exit

# 12. Create storage link
php artisan storage:link

# 13. Optimize for production
php artisan optimize

# 14. Mark as installed
echo "$(date)" > storage/installed
```

### Method 3: Docker Installation

```bash
# 1. Clone repository
git clone https://github.com/Lindon11/LaravelCP.git
cd LaravelCP

# 2. Start containers
docker-compose up -d

# 3. Install dependencies inside container
docker exec laravel_app composer install
docker exec laravel_app npm install

# 4. Copy and configure .env
docker exec laravel_app cp .env.example .env
docker exec laravel_app php artisan key:generate

# Edit .env for Docker:
# DB_HOST=mysql
# DB_PORT=3306
# DB_DATABASE=laravelcp
# DB_USERNAME=laravelcp
# DB_PASSWORD=secret

# 5. Run migrations and seed
docker exec laravel_app php artisan migrate
docker exec laravel_app php artisan db:seed

# 6. Build frontend
docker exec laravel_app npm run build
docker exec laravel_app bash -c "cd resources/admin && npm install && npm run build"

# 7. Create admin user
docker exec -it laravel_app php create_admin.php

# 8. Storage link and optimize
docker exec laravel_app php artisan storage:link
docker exec laravel_app php artisan optimize
```

## Troubleshooting

### Issue: "Module not installed" or "Class not found"

**Cause:** Composer dependencies (`vendor/` folder) are missing.

**Solution:**
```bash
# Navigate to your Laravel directory
cd /path/to/LaravelCP

# Install composer dependencies
composer install --no-dev --optimize-autoloader

# Verify vendor folder exists
ls -la vendor/
```

**Note:** NEVER commit the `vendor/` folder to git. Always run `composer install` after cloning the repository.

### Issue: Modified `public/index.php` causing errors

**Cause:** Someone modified the index.php with custom paths like `.app/repo/LaravelCP`.

**Solution:** Use the standard Laravel index.php (already in the repo):
```php
// public/index.php should have:
require __DIR__.'/../vendor/autoload.php';
(require_once __DIR__.'/../bootstrap/app.php')
```

For custom hosting setups (cPanel/Plesk), only modify the path if absolutely necessary and ensure the path actually exists on your server.

### Issue: "Admin role not found"

**Cause:** Database seeders haven't been run yet.

**Solution:**
```bash
php artisan db:seed
# OR specifically:
php artisan db:seed --class=RolePermissionSeeder
```

### Issue: "Failed to assign admin role to user"

**Cause:** Roles table doesn't exist or isn't populated.

**Solution:**
```bash
# Check if migrations ran
php artisan migrate:status

# If not, run them
php artisan migrate

# Then seed the roles
php artisan db:seed --class=RolePermissionSeeder
```

### Issue: "Database tables not found"

**Cause:** Migrations haven't been run.

**Solution:**
```bash
php artisan migrate
```

### Issue: "User created but can't access admin panel"

**Cause:** Admin role wasn't properly assigned.

**Solution:** Fix the user's role in tinker:
```bash
php artisan tinker
>>> $user = User::where('username', 'admin')->first();
>>> $user->assignRole('admin');
>>> $user->hasRole('admin'); // Should return true
>>> exit
```

### Issue: "Permission denied" errors

**Cause:** File permissions on storage directories.

**Solution:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Verifying Installation

After installation, verify everything works:

```bash
# Check if installed file exists
ls storage/installed

# Check if admin user exists and has role
php artisan tinker
>>> User::where('username', 'admin')->first()->getRoleNames();
# Should show: ["admin"]

# Check if all seeders ran
php artisan tinker
>>> \Spatie\Permission\Models\Role::count();
# Should show: 3 (admin, moderator, user)
>>> \Spatie\Permission\Models\Permission::count();
# Should show: ~20+ permissions

# Test admin panel access
# Visit: http://your-domain/admin/
# Login with your admin credentials
```

## Post-Installation

1. **Remove public installer** (for security):
   ```bash
   rm -rf public/install
   ```

2. **Set up queue worker** (optional but recommended):
   ```bash
   php artisan queue:work
   # OR use supervisor in production
   ```

3. **Set up task scheduler** (for game events):
   ```bash
   # Add to crontab:
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

4. **Configure mail** in `.env` for notifications

5. **Review security settings** in `config/app.php`

## Common Installation Order Mistakes

❌ **WRONG ORDER:**
1. Create database
2. Create admin user ← FAILS (no roles yet)
3. Run migrations ← Creates roles table
4. Run seeders ← Creates admin role

✅ **CORRECT ORDER:**
1. Create database
2. Run migrations ← Creates roles table
3. Run seeders ← Creates admin role
4. Create admin user ← NOW works!

## Getting Help

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode: `APP_DEBUG=true` in `.env`
3. Run in verbose mode: `php artisan migrate -vvv`
4. Check database connection: `php artisan tinker` then `DB::connection()->getPdo()`
5. Create GitHub issue with error details

## Security Notes

- Change `APP_KEY` in production
- Set `APP_DEBUG=false` in production
- Use strong database passwords
- Remove `/install` directory after installation
- Set up HTTPS/SSL certificates
- Configure firewall rules
- Regular backups of database
---

## Deployment to Production Server

### cPanel/Shared Hosting

**Standard Structure:**
```
home/username/
├── LaravelCP/              (outside public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── vendor/             (run composer install here)
│   └── ...
└── public_html/            (web root)
    ├── index.php           (modified)
    ├── .htaccess
    └── assets/
```

**Steps:**

1. **Upload Laravel files** to `~/LaravelCP/` (outside public_html)

2. **SSH into server and install dependencies:**
   ```bash
   cd ~/LaravelCP
   composer install --no-dev --optimize-autoloader
   ```

3. **Copy public folder contents** to `public_html/`:
   ```bash
   cp -r public/* ~/public_html/
   ```

4. **Modify `public_html/index.php`:**
   ```php
   <?php
   use Illuminate\Http\Request;
   
   define('LARAVEL_START', microtime(true));
   
   $base = __DIR__ . '/../LaravelCP';  // Point to Laravel directory
   
   if (file_exists($maintenance = $base . '/storage/framework/maintenance.php')) {
       require $maintenance;
   }
   
   require $base . '/vendor/autoload.php';
   
   (require_once $base . '/bootstrap/app.php')
       ->handleRequest(Request::capture());
   ```

5. **Set up .env file:**
   ```bash
   cd ~/LaravelCP
   cp .env.example .env
   nano .env
   # Configure database, APP_URL, etc.
   php artisan key:generate
   ```

6. **Run migrations and optimize:**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   php artisan optimize
   php artisan storage:link
   ```

7. **Set permissions:**
   ```bash
   chmod -R 755 ~/LaravelCP/storage
   chmod -R 755 ~/LaravelCP/bootstrap/cache
   ```

### VPS/Dedicated Server (Ubuntu/Nginx)

See full deployment guide in the documentation for Nginx configuration, SSL setup, and server optimization.

### Important Deployment Notes

- ✅ Always run `composer install` on the server (never commit `vendor/`)
- ✅ Use `--no-dev` flag in production for smaller footprint
- ✅ Set `APP_ENV=production` and `APP_DEBUG=false`
- ✅ Cache config: `php artisan config:cache`
- ✅ Cache routes: `php artisan route:cache`
- ✅ Optimize autoloader: `composer dump-autoload --optimize`
- ❌ Never commit `.env` file
- ❌ Never commit `vendor/` folder
- ❌ Don't modify `public/index.php` unless necessary