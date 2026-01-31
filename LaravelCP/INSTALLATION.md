# 🚀 Installation Guide

## Quick Install (Recommended for Non-Developers)

We've created an easy interactive installer that does everything for you!

### Requirements

Before running the installer, make sure you have:
- **PHP 8.2+** with extensions: PDO, MySQL, mbstring, openssl, tokenizer, json, curl, zip, gd
- **Composer** (PHP package manager)
- **Node.js 18+** and npm
- **MySQL** or **MariaDB** database
- Web server (Apache/Nginx) or use PHP's built-in server

### Installation Steps

1. **Download or clone the repository**
   ```bash
   git clone https://github.com/yourusername/openpbbg.git
   cd openpbbg/laravel-api
   ```

2. **Run the installer**
   ```bash
   ./install.sh
   ```

3. **Follow the prompts:**
   - System check (automatic)
   - Enter database credentials
   - Configure application settings
   - Create admin account

4. **Start the server**
   ```bash
   php artisan serve
   ```

5. **Access your game**
   - Game: http://localhost:8000
   - Admin Panel: http://localhost:8000/admin

That's it! The installer handles everything else automatically.

---

## Manual Installation (Advanced Users)

If you prefer to install manually or the automatic installer doesn't work:

### 1. Copy Environment File
```bash
cp .env.example .env
```

### 2. Edit .env File
Update these values:
```env
APP_NAME="Your Game Name"
APP_URL=http://localhost:8000

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Install Dependencies
```bash
composer install
npm install
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Create Storage Link
```bash
php artisan storage:link
```

### 7. Build Assets
```bash
npm run build
```

### 8. Create Admin User
```bash
php artisan tinker
```
Then run:
```php
$user = App\Models\User::create([
    'username' => 'admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('your-password'),
    'email_verified_at' => now(),
]);
```

### 9. Start Server
```bash
php artisan serve
```

---

## Production Deployment

For production environments:

### 1. Set Environment
```env
APP_ENV=production
APP_DEBUG=false
```

### 2. Optimize Application
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

### 3. Set Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 4. Configure Web Server

**Nginx Example:**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/laravel-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Apache Example (.htaccess is included in public/):**
- Ensure `mod_rewrite` is enabled
- Point DocumentRoot to `public/` directory

### 5. Setup Cron (Optional)
For scheduled tasks:
```bash
* * * * * cd /var/www/html/laravel-api && php artisan schedule:run >> /dev/null 2>&1
```

### 6. Setup Queue Worker (Optional)
For background jobs:
```bash
php artisan queue:work --daemon
```
Or use Supervisor to keep it running.

---

## Troubleshooting

### Permission Errors
```bash
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Database Connection Failed
- Check database credentials in `.env`
- Ensure database exists
- Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

### Composer/NPM Errors
```bash
composer install --ignore-platform-reqs
npm install --legacy-peer-deps
```

### Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 500 Error After Install
- Check `storage/logs/laravel.log`
- Ensure `.env` file exists
- Run `php artisan key:generate`
- Check file permissions

---

## Post-Installation

### Install Modules
1. Go to **Admin Panel → Admin Panel → Module Manager**
2. Browse available modules
3. Click "Install" on desired modules
4. Enable modules after installation

### Configure Game Settings
1. Go to **Admin Panel → Settings**
2. Adjust game parameters
3. Set up crime difficulty, economy rates, etc.

### Add Content
- Create crimes (Admin → Crimes)
- Add items (Admin → Items)
- Set up locations (Admin → Locations)
- Configure gangs (Admin → Gangs)

---

## Need Help?

- 📖 [Quick Start Guide](QUICKSTART.md)
- 🧩 [Module System](MODULE_MANAGER.md)
- 🔌 [API Documentation](API_IMPLEMENTATION.md)
- 💬 Community Forum: [link to your forum]
- 🐛 Report Issues: [link to GitHub issues]

---

## System Requirements

### Minimum
- PHP 8.2+
- MySQL 5.7+ or MariaDB 10.3+
- 512MB RAM
- 1GB disk space

### Recommended
- PHP 8.3+
- MySQL 8.0+ or MariaDB 10.6+
- 2GB RAM
- 5GB disk space
- Redis for caching
- Supervisor for queue management
