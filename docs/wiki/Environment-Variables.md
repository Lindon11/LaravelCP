# Environment Variables

Complete reference for all LaravelCP environment variables.

---

## Application

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_NAME` | LaravelCP | Application name |
| `APP_ENV` | production | Environment: local, staging, production |
| `APP_KEY` | - | Application encryption key (auto-generated) |
| `APP_DEBUG` | false | Enable debug mode (disable in production) |
| `APP_TIMEZONE` | UTC | Application timezone |
| `APP_URL` | http://localhost | Application base URL |
| `APP_LOCALE` | en | Default locale |

---

## Database

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_CONNECTION` | mysql | Database driver |
| `DB_HOST` | 127.0.0.1 | Database host |
| `DB_PORT` | 3306 | Database port |
| `DB_DATABASE` | laravelcp | Database name |
| `DB_USERNAME` | root | Database username |
| `DB_PASSWORD` | - | Database password |

---

## Cache & Session

| Variable | Default | Description |
|----------|---------|-------------|
| `CACHE_DRIVER` | file | Cache driver: file, redis, memcached |
| `CACHE_PREFIX` | laravelcp | Cache key prefix |
| `SESSION_DRIVER` | file | Session driver: file, redis, database |
| `SESSION_LIFETIME` | 120 | Session lifetime in minutes |
| `SESSION_DOMAIN` | null | Cookie domain |
| `SESSION_SECURE_COOKIE` | false | HTTPS only cookies |

---

## Queue

| Variable | Default | Description |
|----------|---------|-------------|
| `QUEUE_CONNECTION` | sync | Queue driver: sync, redis, database |
| `QUEUE_FAILED_DRIVER` | database-uuids | Failed job driver |

---

## Redis

| Variable | Default | Description |
|----------|---------|-------------|
| `REDIS_HOST` | 127.0.0.1 | Redis server host |
| `REDIS_PASSWORD` | null | Redis password |
| `REDIS_PORT` | 6379 | Redis port |
| `REDIS_CLIENT` | phpredis | Redis client: phpredis, predis |

---

## Mail

| Variable | Default | Description |
|----------|---------|-------------|
| `MAIL_MAILER` | smtp | Mail driver: smtp, mailgun, ses |
| `MAIL_HOST` | mailhog | SMTP host |
| `MAIL_PORT` | 1025 | SMTP port |
| `MAIL_USERNAME` | null | SMTP username |
| `MAIL_PASSWORD` | null | SMTP password |
| `MAIL_ENCRYPTION` | null | Encryption: tls, ssl |
| `MAIL_FROM_ADDRESS` | noreply@example.com | Default from address |
| `MAIL_FROM_NAME` | ${APP_NAME} | Default from name |

---

## Authentication

| Variable | Default | Description |
|----------|---------|-------------|
| `SANCTUM_STATEFUL_DOMAINS` | localhost | Comma-separated stateful domains |
| `SESSION_DOMAIN` | null | Session cookie domain |

---

## OAuth Providers

### Discord

| Variable | Description |
|----------|-------------|
| `DISCORD_CLIENT_ID` | Discord OAuth client ID |
| `DISCORD_CLIENT_SECRET` | Discord OAuth secret |
| `DISCORD_REDIRECT_URI` | OAuth callback URL |

### Google

| Variable | Description |
|----------|-------------|
| `GOOGLE_CLIENT_ID` | Google OAuth client ID |
| `GOOGLE_CLIENT_SECRET` | Google OAuth secret |
| `GOOGLE_REDIRECT_URI` | OAuth callback URL |

### GitHub

| Variable | Description |
|----------|-------------|
| `GITHUB_CLIENT_ID` | GitHub OAuth client ID |
| `GITHUB_CLIENT_SECRET` | GitHub OAuth secret |
| `GITHUB_REDIRECT_URI` | OAuth callback URL |

---

## CORS

| Variable | Default | Description |
|----------|---------|-------------|
| `CORS_ALLOWED_ORIGINS` | * | Allowed origins (comma-separated) |

---

## Logging

| Variable | Default | Description |
|----------|---------|-------------|
| `LOG_CHANNEL` | stack | Log channel |
| `LOG_DEPRECATIONS_CHANNEL` | null | Deprecation warnings channel |
| `LOG_LEVEL` | debug | Minimum log level |

---

## Plugins

| Variable | Default | Description |
|----------|---------|-------------|
| `PLUGINS_CACHE` | true | Cache plugin discovery |

---

## Filesystem

| Variable | Default | Description |
|----------|---------|-------------|
| `FILESYSTEM_DISK` | local | Default filesystem disk |

---

## AWS (Optional)

| Variable | Description |
|----------|-------------|
| `AWS_ACCESS_KEY_ID` | AWS access key |
| `AWS_SECRET_ACCESS_KEY` | AWS secret |
| `AWS_DEFAULT_REGION` | AWS region |
| `AWS_BUCKET` | S3 bucket name |

---

## Example `.env` Files

### Development

```env
APP_NAME=LaravelCP
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravelcp
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

### Docker Development

```env
APP_NAME=LaravelCP
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8001

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

REDIS_HOST=redis

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

### Production

```env
APP_NAME=MyGame
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mygame.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=mygame_prod
DB_USERNAME=mygame_user
DB_PASSWORD=strong_password_here

REDIS_HOST=your-redis-host
REDIS_PASSWORD=redis_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

SESSION_DOMAIN=.mygame.com
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_mailgun_user
MAIL_PASSWORD=your_mailgun_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mygame.com
MAIL_FROM_NAME="My Game"

SANCTUM_STATEFUL_DOMAINS=mygame.com,www.mygame.com
CORS_ALLOWED_ORIGINS=https://mygame.com,https://www.mygame.com
```

---

## Security Notes

1. **Never commit `.env` to version control**
2. **Use strong, unique passwords**
3. **Set `APP_DEBUG=false` in production**
4. **Use HTTPS in production**
5. **Restrict `CORS_ALLOWED_ORIGINS` in production**
6. **Rotate `APP_KEY` if compromised**

---

## Generating Keys

### Application Key

```bash
php artisan key:generate
```

### Creating Secure Passwords

```bash
# Generate random string
openssl rand -base64 32
```
