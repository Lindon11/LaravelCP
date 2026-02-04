# Production Deployment

Guide to deploying LaravelCP on a production server.

---

## Server Requirements

- **OS**: Ubuntu 20.04+ / Debian 11+ / CentOS 8+
- **PHP**: 8.3+ with extensions
- **MySQL**: 8.0+ or MariaDB 10.5+
- **Nginx** or Apache
- **Composer** 2.0+
- **Node.js** 18+ (for building admin panel)
- **RAM**: 2GB minimum, 4GB recommended
- **Storage**: 20GB minimum

---

## Server Setup

### 1. Install Dependencies (Ubuntu)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-gd php8.3-zip php8.3-bcmath \
    php8.3-redis php8.3-intl

# Install MySQL 8.0
sudo apt install -y mysql-server

# Install Nginx
sudo apt install -y nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Redis (optional but recommended)
sudo apt install -y redis-server

# Install Supervisor (for queue workers)
sudo apt install -y supervisor

# Install Git
sudo apt install -y git
```

### 2. Configure MySQL

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE laravelcp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'laravelcp'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON laravelcp.* TO 'laravelcp'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Configure PHP-FPM

Edit `/etc/php/8.3/fpm/pool.d/www.conf`:

```ini
[www]
user = www-data
group = www-data

listen = /run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

php_admin_value[memory_limit] = 512M
php_admin_value[upload_max_filesize] = 100M
php_admin_value[post_max_size] = 100M
```

Edit `/etc/php/8.3/fpm/php.ini`:

```ini
upload_max_filesize = 100M
post_max_size = 100M
memory_limit = 512M
max_execution_time = 300
opcache.enable = 1
opcache.memory_consumption = 256
opcache.max_accelerated_files = 20000
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.3-fpm
```

---

## Application Deployment

### 1. Clone Repository

```bash
# Create web directory
sudo mkdir -p /var/www/laravelcp
sudo chown $USER:$USER /var/www/laravelcp

# Clone repository
cd /var/www
git clone https://github.com/Lindon11/LaravelCP.git laravelcp
cd laravelcp
```

### 2. Install Dependencies

```bash
# Install PHP dependencies (production mode)
composer install --optimize-autoloader --no-dev

# Install Node dependencies and build admin panel
npm install
npm run build
```

### 3. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file
nano .env
```

**Production .env:**

```env
APP_NAME="Your Game Name"
APP_ENV=production
APP_KEY=base64:your_generated_key
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravelcp
DB_USERNAME=laravelcp
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Run Migrations

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 5. Set Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/laravelcp

# Set directory permissions
sudo find /var/www/laravelcp -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/laravelcp -type f -exec chmod 644 {} \;

# Make storage and cache writable
sudo chmod -R 775 /var/www/laravelcp/storage
sudo chmod -R 775 /var/www/laravelcp/bootstrap/cache
```

### 6. Optimize Application

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Clear compiled classes
php artisan optimize
```

---

## Nginx Configuration

Create `/etc/nginx/sites-available/laravelcp`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/laravelcp/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    index index.php;

    charset utf-8;

    # Main application
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Admin panel SPA
    location /admin {
        alias /var/www/laravelcp/public/admin;
        try_files $uri $uri/ /admin/index.html;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/laravelcp /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```

---

## SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

Certbot will automatically update your Nginx configuration for HTTPS.

---

## Queue Worker Setup

Create `/etc/supervisor/conf.d/laravelcp-worker.conf`:

```ini
[program:laravelcp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravelcp/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/laravelcp/storage/logs/worker.log
stopwaitsecs=3600
```

Start workers:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravelcp-worker:*
```

---

## Cron Jobs

Add scheduler to crontab:

```bash
sudo crontab -u www-data -e
```

Add this line:

```cron
* * * * * cd /var/www/laravelcp && php artisan schedule:run >> /dev/null 2>&1
```

---

## Firewall Configuration

```bash
# Allow SSH, HTTP, HTTPS
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

---

## Deployment Script

Create `deploy.sh` in your project root:

```bash
#!/bin/bash
set -e

echo "🚀 Starting deployment..."

# Pull latest changes
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Restart queue workers
sudo supervisorctl restart laravelcp-worker:*

# Clear OPcache
sudo systemctl reload php8.3-fpm

echo "✅ Deployment complete!"
```

Make it executable:

```bash
chmod +x deploy.sh
```

---

## Monitoring

### Log Files

```bash
# Application logs
tail -f /var/www/laravelcp/storage/logs/laravel.log

# Nginx error log
tail -f /var/log/nginx/error.log

# PHP-FPM log
tail -f /var/log/php8.3-fpm.log

# Queue worker log
tail -f /var/www/laravelcp/storage/logs/worker.log
```

### Health Check Endpoint

Add to your routes:

```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});
```

---

## Backup Strategy

### Daily Database Backup

Create `/etc/cron.daily/backup-laravelcp`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/laravelcp"
DATE=$(date +%Y-%m-%d)

mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u laravelcp -p'your_password' laravelcp | gzip > $BACKUP_DIR/db-$DATE.sql.gz

# Backup uploaded files
tar -czf $BACKUP_DIR/uploads-$DATE.tar.gz /var/www/laravelcp/storage/app/public

# Keep only last 7 days
find $BACKUP_DIR -type f -mtime +7 -delete
```

Make executable:

```bash
sudo chmod +x /etc/cron.daily/backup-laravelcp
```

---

## Next Steps

- [Docker Setup](Docker-Setup) - Docker deployment alternative
- [Environment Variables](Environment-Variables) - Configuration reference
- [Troubleshooting](Troubleshooting) - Common issues
