# Configuration

Complete guide to configuring LaravelCP for your game.

---

## Environment Variables

The `.env` file controls your application's configuration. Copy from `.env.example`:

```bash
cp .env.example .env
```

### Application Settings

```env
# Application name shown in UI and emails
APP_NAME="LaravelCP"

# Environment: local, staging, production
APP_ENV=production

# Debug mode (disable in production!)
APP_DEBUG=false

# Your application URL
APP_URL=https://yourgame.com

# Timezone
APP_TIMEZONE=UTC

# Locale
APP_LOCALE=en
```

### Database Configuration

```env
# Database driver (mysql recommended)
DB_CONNECTION=mysql

# Database host (use 'mysql' for Docker)
DB_HOST=127.0.0.1

# Database port
DB_PORT=3306

# Database name
DB_DATABASE=laravelcp

# Database credentials
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Cache & Session

```env
# Cache driver: file, redis, memcached, database
CACHE_DRIVER=redis

# Session driver: file, redis, database, cookie
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Queue driver: sync, redis, database
QUEUE_CONNECTION=redis
```

### Redis (Recommended)

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Email Configuration

```env
# Email driver: smtp, mailgun, ses, postmark
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourgame.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### API & Security

```env
# API sanctum domains (for SPA authentication)
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1

# Session domain for cookies
SESSION_DOMAIN=localhost

# CORS allowed origins
CORS_ALLOWED_ORIGINS=http://localhost:5173
```

### OAuth Providers (Optional)

```env
# Discord
DISCORD_CLIENT_ID=
DISCORD_CLIENT_SECRET=
DISCORD_REDIRECT_URI="${APP_URL}/api/oauth/discord/callback"

# Google
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/api/oauth/google/callback"

# GitHub
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI="${APP_URL}/api/oauth/github/callback"
```

---

## Configuration Files

### `config/app.php`

Main application configuration:

```php
return [
    'name' => env('APP_NAME', 'LaravelCP'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    
    // Service providers
    'providers' => ServiceProvider::defaultProviders()->merge([
        // LaravelCP providers
        App\Core\Providers\AppServiceProvider::class,
        App\Core\Providers\PluginServiceProvider::class,
    ])->toArray(),
    
    // Aliases
    'aliases' => Facade::defaultAliases()->merge([
        'Hook' => App\Facades\Hook::class,
    ])->toArray(),
];
```

### `config/plugins.php`

Plugin system configuration:

```php
return [
    // Plugin directory path
    'path' => app_path('Plugins'),
    
    // Plugin namespace
    'namespace' => 'App\\Plugins',
    
    // Auto-discover plugins on boot
    'auto_discover' => true,
    
    // Cache plugin discovery
    'cache' => env('PLUGINS_CACHE', true),
    
    // Expected directory structure
    'structure' => [
        'routes' => ['web.php', 'api.php', 'admin.php'],
        'controllers' => 'Controllers',
        'models' => 'Models',
        'views' => 'views',
        'migrations' => 'database/migrations',
        'seeders' => 'database/seeders',
        'hooks' => 'hooks.php',
        'config' => 'config.php',
        'assets' => 'assets',
        'lang' => 'lang',
    ],
    
    // Middleware for plugin routes
    'middleware' => [
        'api' => ['api', 'auth:sanctum'],
        'web' => ['web'],
        'admin' => ['api', 'auth:sanctum', 'role:admin'],
    ],
];
```

### `config/auth.php`

Authentication configuration:

```php
return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],
    
    'guards' => [
        'api' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],
    ],
    
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Core\Models\User::class,
        ],
    ],
    
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],
];
```

### `config/sanctum.php`

API token configuration:

```php
return [
    // Domains where cookies will be sent (SPA)
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', '')),
    
    // Token expiration (null = never expires)
    'expiration' => null,
    
    // Middleware for stateful requests
    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];
```

### `config/cors.php`

Cross-Origin Resource Sharing:

```php
return [
    'paths' => ['api/*'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => true,
];
```

---

## Game Settings (Database)

Game settings are stored in the `settings` table and managed through the admin panel.

### Accessing Settings

```php
use App\Core\Models\Setting;

// Get a setting
$gameName = Setting::get('game.name', 'Default Name');

// Get multiple settings
$settings = Setting::getGroup('game');

// Set a setting
Setting::set('game.name', 'My Awesome Game');
```

### Common Game Settings

| Key | Description | Default |
|-----|-------------|---------|
| `game.name` | Game name | LaravelCP |
| `game.description` | Game description | - |
| `game.starting_cash` | New player cash | 1000 |
| `game.starting_bank` | New player bank | 0 |
| `game.energy_regen` | Energy per minute | 2 |
| `game.health_regen` | Health per minute | 1 |
| `game.max_level` | Maximum level | 100 |
| `registration.enabled` | Allow new registrations | true |
| `registration.verify_email` | Require email verification | false |

### Managing via Admin Panel

Navigate to **Admin Panel → Settings** to manage all game settings through a user-friendly interface.

---

## Cache Configuration

### File Cache (Default)

```env
CACHE_DRIVER=file
```

### Redis Cache (Recommended)

```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Clear Cache

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Or use optimize:clear
php artisan optimize:clear
```

---

## Queue Configuration

### Sync (Development)

```env
QUEUE_CONNECTION=sync
```

Jobs run immediately (synchronously).

### Redis (Production)

```env
QUEUE_CONNECTION=redis
```

Run queue worker:

```bash
php artisan queue:work
```

For production, use Supervisor:

```ini
[program:laravelcp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravelcp/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/laravelcp/storage/logs/worker.log
```

---

## Scheduled Tasks

### Configure Cron

Add to your server's crontab:

```bash
* * * * * cd /path/to/laravelcp && php artisan schedule:run >> /dev/null 2>&1
```

### View Scheduled Tasks

```bash
php artisan schedule:list
```

### Common Scheduled Tasks

- Daily rewards reset
- Energy regeneration
- Hospital discharge
- Jail release
- Stock price updates
- Lottery draws

---

## Security Configuration

### Production Checklist

```env
# MUST set in production
APP_ENV=production
APP_DEBUG=false

# Strong app key (auto-generated)
APP_KEY=base64:...

# HTTPS
APP_URL=https://yourgame.com

# Specific domains only
SANCTUM_STATEFUL_DOMAINS=yourgame.com,www.yourgame.com
SESSION_DOMAIN=.yourgame.com
```

### Rate Limiting

Configure in `routes/api.php`:

```php
// Login attempts: 10 per minute
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// General API: 60 per minute
Route::middleware('throttle:60,1')->group(function () {
    // API routes
});
```

### HTTPS Enforcement

In `app/Http/Middleware/TrustProxies.php`:

```php
protected $proxies = '*';
protected $headers = Request::HEADER_X_FORWARDED_FOR |
                     Request::HEADER_X_FORWARDED_HOST |
                     Request::HEADER_X_FORWARDED_PORT |
                     Request::HEADER_X_FORWARDED_PROTO;
```

In `app/Providers/AppServiceProvider.php`:

```php
public function boot(): void
{
    if (config('app.env') === 'production') {
        URL::forceScheme('https');
    }
}
```

---

## Development vs Production

### Development

```env
APP_ENV=local
APP_DEBUG=true
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Next Steps

- [Docker Setup](Docker-Setup) - Container configuration
- [Production Deployment](Production-Deployment) - Deploy to server
- [Environment Variables](Environment-Variables) - Full reference
