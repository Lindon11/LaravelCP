# LaravelCP Deployment Guide

## Table of Contents
- [Web Installer (Easiest)](#web-installer-easiest)
- [Requirements](#requirements)
- [Local Development Setup](#local-development-setup)
- [Production Deployment](#production-deployment)
- [Environment Configuration](#environment-configuration)
- [Admin Panel Setup](#admin-panel-setup)
- [Troubleshooting](#troubleshooting)

---

## Web Installer (Easiest)

LaravelCP includes a **browser-based installer** - perfect for shared hosting, cPanel, or anyone who prefers not to use the command line.

### Quick Steps

1. **Upload LaravelCP files** to your web server
   - Via FTP, cPanel File Manager, or git clone

2. **Create an empty MySQL database**
   - Note your database name, username, and password

3. **Set folder permissions** (if needed)
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

4. **Open the installer in your browser:**
   ```
   https://your-domain.com/install
   ```

5. **Follow the 6-step wizard:**
   - ✅ Requirements check (PHP version, extensions, permissions)
   - ⚙️ Database configuration (with live connection test)
   - 🎮 Game settings (name, URL)
   - 👤 Admin account creation
   - 📦 Installation (migrations, seeding, optimization)
   - 🎉 Complete!

The installer handles everything: database setup, migrations, seeding game data, creating your admin account, and optimizing for production.

**After installation:**
- Admin Panel: `https://your-domain.com/admin`
- API: `https://your-domain.com/api`

---

## Requirements

### Development Environment
- Docker & Docker Compose (recommended)
- OR PHP 8.3+, MySQL 8.0+, Redis, Composer, Node.js 20+

### Production Server
- Ubuntu 20.04+ / Debian 11+ (recommended)
- PHP 8.3+ with extensions: `gd`, `pdo_mysql`, `zip`, `bcmath`, `mbstring`, `curl`, `xml`
- MySQL 8.0+ or MariaDB 10.6+
- Redis 6.0+
- Nginx or Apache
- Composer 2.x
- Node.js 20+ & NPM
- SSL Certificate (Let's Encrypt recommended)
- Minimum 2GB RAM, 2 CPU cores

---

## Local Development Setup

### Using Docker (Recommended)

1. **Clone the repository**
   ```bash
   cd /path/to/your/projects
   git clone https://github.com/Lindon11/LaravelCP.git
   cd LaravelCP
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Update `.env` for Docker**
   ```env
   APP_NAME=LaravelCP
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8001

   DB_CONNECTION=mysql
   DB_HOST=laravel_db
   DB_PORT=3306
   DB_DATABASE=laravel_db
   DB_USERNAME=laravel_user
   DB_PASSWORD=laravel_password

   REDIS_HOST=redis
   REDIS_PASSWORD=null
   REDIS_PORT=6379

   SANCTUM_STATEFUL_DOMAINS=localhost:8001,localhost:5175
   SESSION_DOMAIN=localhost
   ```

4. **Start Docker containers**
   ```bash
   docker-compose up -d
   ```

5. **Install PHP dependencies**
   ```bash
   docker exec laravel_app composer install
   ```

6. **Generate application key**
   ```bash
   docker exec laravel_app php artisan key:generate
   ```

7. **Run migrations and seeders**
   ```bash
   docker exec laravel_app php artisan migrate --seed
   ```

8. **Install Node.js dependencies for admin panel**
   ```bash
   docker exec laravel_app bash -c "cd resources/admin && npm install"
   ```

9. **Build admin panel**
   ```bash
   docker exec laravel_app bash -c "cd resources/admin && npm run build"
   ```

10. **Access the application**
    - API: http://localhost:8001/api
    - Admin Panel: http://localhost:8001/admin
    - Default Admin Login: `admin@admin.com` / `password`

### Without Docker

1. **Install dependencies**
   ```bash
   composer install
   cd resources/admin && npm install
   ```

2. **Setup database**
   - Create MySQL database
   - Update `.env` with database credentials

3. **Run setup commands**
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   cd resources/admin && npm run build
   ```

4. **Start development server**
   ```bash
   php artisan serve --port=8001
   ```

---

## Production Deployment

### 1. Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3 and extensions
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-redis \
    php8.3-gd php8.3-zip php8.3-bcmath php8.3-mbstring php8.3-curl php8.3-xml

# Install MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install Redis
sudo apt install -y redis-server
sudo systemctl enable redis-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install -y nginx
```

### 2. Application Deployment

```bash
# Create web directory
sudo mkdir -p /var/www/laravelcp
sudo chown -R $USER:www-data /var/www/laravelcp

# Clone repository
cd /var/www/laravelcp
git clone https://github.com/Lindon11/LaravelCP.git .

# Set permissions
sudo chown -R www-data:www-data /var/www/laravelcp
sudo chmod -R 755 /var/www/laravelcp
sudo chmod -R 775 /var/www/laravelcp/storage
sudo chmod -R 775 /var/www/laravelcp/bootstrap/cache
```

### 3. Environment Configuration

```bash
# Copy and edit environment file
cp .env.example .env
nano .env
```

**Production `.env` settings:**
```env
APP_NAME=LaravelCP
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravelcp_prod
DB_USERNAME=laravelcp_user
DB_PASSWORD=your_secure_password_here

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

SANCTUM_STATEFUL_DOMAINS=api.yourdomain.com,app.yourdomain.com
SESSION_DOMAIN=.yourdomain.com
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Install Dependencies

```bash
cd /var/www/laravelcp

# Install PHP dependencies (production optimized)
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Install Node dependencies for admin panel
cd resources/admin
npm install

# Build admin panel for production
npm run build

# Return to root
cd /var/www/laravelcp
```

### 5. Database Setup

```bash
# Create database
sudo mysql -u root -p
```

```sql
CREATE DATABASE laravelcp_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'laravelcp_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';
GRANT ALL PRIVILEGES ON laravelcp_prod.* TO 'laravelcp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Run migrations
php artisan migrate --force

# Seed database (only on first deployment)
php artisan db:seed --force

# Create storage link
php artisan storage:link

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Nginx Configuration

Create `/etc/nginx/sites-available/laravelcp`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name api.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.yourdomain.com;

    root /var/www/laravelcp/public;
    index index.php index.html;

    # SSL Configuration (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/api.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Logs
    access_log /var/log/nginx/laravelcp-access.log;
    error_log /var/log/nginx/laravelcp-error.log;

    # Max upload size
    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

**Enable site:**
```bash
sudo ln -s /etc/nginx/sites-available/laravelcp /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d api.yourdomain.com

# Auto-renewal is configured automatically
```

### 8. Setup Supervisor for Queue Workers

Create `/etc/supervisor/conf.d/laravelcp-worker.conf`:

```ini
[program:laravelcp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravelcp/artisan queue:work --sleep=3 --tries=3 --max-time=3600
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

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravelcp-worker:*
```

### 9. Setup Cron Jobs

```bash
sudo crontab -e -u www-data
```

Add:
```cron
* * * * * cd /var/www/laravelcp && php artisan schedule:run >> /dev/null 2>&1
```

### 10. File Permissions (Final Check)

```bash
cd /var/www/laravelcp
sudo chown -R www-data:www-data .
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;
sudo chmod -R 775 storage bootstrap/cache
```

---

## Environment Configuration

### Key Environment Variables

| Variable | Development | Production | Description |
|----------|-------------|------------|-------------|
| `APP_ENV` | local | production | Application environment |
| `APP_DEBUG` | true | false | Debug mode |
| `APP_URL` | http://localhost:8001 | https://api.yourdomain.com | Application URL |
| `DB_HOST` | laravel_db | 127.0.0.1 | Database host |
| `SANCTUM_STATEFUL_DOMAINS` | localhost:8001,localhost:5175 | api.yourdomain.com,app.yourdomain.com | CORS domains |
| `SESSION_DOMAIN` | localhost | .yourdomain.com | Cookie domain |
| `SESSION_SECURE_COOKIE` | false | true | HTTPS-only cookies |

---

## Admin Panel Setup

The admin panel is a Vue 3 SPA located in `resources/admin/`.

### Development
```bash
cd resources/admin
npm run dev
```

### Production Build
```bash
cd resources/admin
npm run build
```

Built files are output to `public/admin/` and served at `/admin` route.

### Admin Panel Features
- User Management (roles: admin, moderator, staff, user, super_admin)
- Game Configuration: Settings, Locations, Ranks
- Crime System: Crimes, Organized Crimes
- Economy: Drugs, Items, Properties, Cars
- Combat & Social: Bounties, Gangs
- Content: Announcements, FAQ, Wiki, Tickets, Error Logs

### Default Admin Account
```
Email: admin@admin.com
Password: password
Role: admin
```

**⚠️ IMPORTANT:** Change default password immediately in production!

---

## Updating Production Application

```bash
cd /var/www/laravelcp

# Enable maintenance mode
php artisan down

# Pull latest changes
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Rebuild admin panel
cd resources/admin
npm install
npm run build
cd /var/www/laravelcp

# Run migrations
php artisan migrate --force

# Clear and rebuild cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo supervisorctl restart laravelcp-worker:*
sudo systemctl reload php8.3-fpm

# Disable maintenance mode
php artisan up
```

---

## Troubleshooting

### 500 Internal Server Error
```bash
# Check logs
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/laravelcp-error.log

# Ensure permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### Admin Panel Not Loading
```bash
# Rebuild admin panel
cd resources/admin
npm install
npm run build

# Check if files exist
ls -la ../../public/admin/
```

### Database Connection Failed
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check credentials in .env
# Verify database exists
mysql -u laravelcp_user -p laravelcp_prod
```

### Queue Jobs Not Processing
```bash
# Check supervisor status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart laravelcp-worker:*

# Check worker logs
tail -f storage/logs/worker.log
```

### CORS Issues
- Ensure `SANCTUM_STATEFUL_DOMAINS` includes frontend domain
- Check `SESSION_DOMAIN` is set to parent domain with dot prefix
- Verify `SESSION_SECURE_COOKIE=true` in production with HTTPS

### Redis Connection Failed
```bash
# Check Redis status
sudo systemctl status redis-server

# Test connection
redis-cli ping
# Should return: PONG
```

---

## Security Checklist

- [ ] Change default admin password
- [ ] Set `APP_DEBUG=false` in production
- [ ] Configure SSL certificate
- [ ] Set secure session cookies (`SESSION_SECURE_COOKIE=true`)
- [ ] Configure firewall (UFW)
- [ ] Disable directory listing in web server
- [ ] Regular backups of database and files
- [ ] Keep dependencies updated
- [ ] Monitor error logs
- [ ] Use strong database passwords
- [ ] Restrict database access to localhost only
- [ ] Configure proper file permissions

---

## Backup Strategy

### Database Backup
```bash
# Create backup
mysqldump -u laravelcp_user -p laravelcp_prod > backup-$(date +%Y%m%d-%H%M%S).sql

# Automate with cron (daily at 2 AM)
0 2 * * * mysqldump -u laravelcp_user -p'password' laravelcp_prod | gzip > /backup/db-$(date +\%Y\%m\%d).sql.gz
```

### File Backup
```bash
# Backup storage and uploads
tar -czf backup-files-$(date +%Y%m%d).tar.gz storage/ public/storage/

# Automate with cron (daily at 3 AM)
0 3 * * * tar -czf /backup/files-$(date +\%Y\%m\%d).tar.gz /var/www/laravelcp/storage /var/www/laravelcp/public/storage
```

---

## Performance Optimization

### Enable OPcache
Edit `/etc/php/8.3/fpm/php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### Enable Redis Cache
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Enable Gzip Compression
Add to Nginx config:
```nginx
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;
```

---

## Support

For issues and questions:
- GitHub Issues: https://github.com/Lindon11/LaravelCP/issues
- Documentation: This file
- Laravel Docs: https://laravel.com/docs

---

**Last Updated:** January 2026
