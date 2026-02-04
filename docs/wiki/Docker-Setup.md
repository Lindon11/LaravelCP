# Docker Setup

Complete guide to running LaravelCP with Docker.

---

## Prerequisites

- Docker Desktop or Docker Engine
- Docker Compose v2.0+
- 4GB RAM minimum

---

## Quick Start

```bash
# Clone repository
git clone https://github.com/Lindon11/LaravelCP.git
cd LaravelCP

# Copy environment file
cp .env.example .env

# Start containers
docker compose up -d

# Install dependencies
docker compose exec app composer install

# Generate app key
docker compose exec app php artisan key:generate

# Run migrations
docker compose exec app php artisan migrate

# Seed database
docker compose exec app php artisan db:seed

# Access at http://localhost:8080
```

---

## Docker Compose Configuration

### docker-compose.yml

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    image: laravelcp-app
    container_name: laravelcp-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
      - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    networks:
      - laravelcp
    depends_on:
      - mysql
      - redis

  nginx:
    image: nginx:alpine
    container_name: laravelcp-nginx
    restart: unless-stopped
    ports:
      - "8080:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
      - ./docker/nginx/ssl:/etc/nginx/ssl
    networks:
      - laravelcp
    depends_on:
      - app

  mysql:
    image: mysql:8.0
    container_name: laravelcp-mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE:-laravelcp}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD:-secret}
      MYSQL_PASSWORD: ${DB_PASSWORD:-secret}
      MYSQL_USER: ${DB_USERNAME:-laravelcp}
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/my.cnf
    networks:
      - laravelcp

  redis:
    image: redis:alpine
    container_name: laravelcp-redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    networks:
      - laravelcp

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: laravelcp-phpmyadmin
    restart: unless-stopped
    environment:
      PMA_HOST: mysql
      PMA_PORT: 3306
      UPLOAD_LIMIT: 300M
    ports:
      - "8081:80"
    networks:
      - laravelcp
    depends_on:
      - mysql

networks:
  laravelcp:
    driver: bridge

volumes:
  mysql_data:
  redis_data:
```

---

## Dockerfile

```dockerfile
FROM php:8.3-fpm

# Arguments
ARG user=www
ARG uid=1000

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    supervisor \
    cron

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

# Copy existing application
COPY --chown=$user:$user . /var/www

USER $user

EXPOSE 9000
CMD ["php-fpm"]
```

---

## Nginx Configuration

### docker/nginx/conf.d/app.conf

```nginx
server {
    listen 80;
    index index.php index.html;
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
    root /var/www/public;

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_read_timeout 300;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
        gzip_static on;
    }
    
    # Admin panel (SPA)
    location /admin {
        try_files $uri $uri/ /admin/index.html;
    }
    
    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

---

## PHP Configuration

### docker/php/local.ini

```ini
upload_max_filesize = 100M
post_max_size = 100M
memory_limit = 512M
max_execution_time = 300
max_input_time = 300

[opcache]
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
opcache.save_comments=1
```

---

## MySQL Configuration

### docker/mysql/my.cnf

```ini
[mysqld]
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
max_connections = 200
slow_query_log = 1
long_query_time = 2
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

[client]
default-character-set = utf8mb4
```

---

## Environment Configuration

### .env for Docker

```env
APP_NAME=LaravelCP
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080

# Docker MySQL settings
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravelcp
DB_USERNAME=laravelcp
DB_PASSWORD=secret

# Docker Redis settings
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379

# Use file for development
LOG_CHANNEL=daily
```

---

## Common Commands

### Container Management

```bash
# Start containers
docker compose up -d

# Stop containers
docker compose down

# Restart containers
docker compose restart

# View logs
docker compose logs -f

# View specific service logs
docker compose logs -f app

# Rebuild containers
docker compose up -d --build
```

### Laravel Commands

```bash
# Run artisan commands
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan queue:work

# Composer commands
docker compose exec app composer install
docker compose exec app composer update
docker compose exec app composer dump-autoload

# Run tests
docker compose exec app php artisan test
```

### Database Commands

```bash
# Access MySQL CLI
docker compose exec mysql mysql -u laravelcp -p laravelcp

# Backup database
docker compose exec mysql mysqldump -u laravelcp -p laravelcp > backup.sql

# Restore database
docker compose exec -T mysql mysql -u laravelcp -p laravelcp < backup.sql
```

### Bash Access

```bash
# Access app container
docker compose exec app bash

# Access as root
docker compose exec -u root app bash

# Access MySQL container
docker compose exec mysql bash
```

---

## Queue Worker (Production)

### docker/supervisor/supervisord.conf

```ini
[supervisord]
nodaemon=true
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=/usr/local/sbin/php-fpm
autostart=true
autorestart=true
priority=5

[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/storage/logs/worker.log
stopwaitsecs=3600
```

---

## Troubleshooting

### Permission Issues

```bash
# Fix storage permissions
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www:www storage bootstrap/cache
```

### MySQL Connection Refused

```bash
# Wait for MySQL to be ready
docker compose exec app php -r "while(!@mysqli_connect('mysql', 'laravelcp', 'secret', 'laravelcp')) { echo 'Waiting...'; sleep(1); }"
```

### Container Won't Start

```bash
# Check logs
docker compose logs app

# Rebuild without cache
docker compose build --no-cache app
```

### Clear Everything

```bash
# Remove all containers, volumes, and networks
docker compose down -v --remove-orphans

# Prune unused resources
docker system prune -af
```

---

## Next Steps

- [Production Deployment](Production-Deployment) - Server deployment guide
- [Environment Variables](Environment-Variables) - Configuration options
- [Installation Guide](Installation-Guide) - Other installation methods
