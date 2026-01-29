# Admin Subdomain Configuration

The admin panel has been configured to run on a separate subdomain: `admin.gangster-legends.orb.local`

## Configuration Changes Made

1. **Filament Panel Provider** - Added domain configuration
   - File: `laravel-api/app/Providers/Filament/AdminPanelProvider.php`
   - Added: `->domain(env('ADMIN_DOMAIN', null))`

2. **Environment Variable** - Added admin domain
   - File: `laravel-api/.env`
   - Added: `ADMIN_DOMAIN=admin.gangster-legends.orb.local`

3. **AppLayout Navigation** - Updated admin link to use subdomain
   - File: `laravel-api/resources/js/Layouts/AppLayout.vue`
   - Changed: `href="/admin"` to `href="http://admin.gangster-legends.orb.local"`

## Setup Required

### 1. Add to your macOS hosts file

```bash
sudo nano /etc/hosts
```

Add this line:
```
127.0.0.1 admin.gangster-legends.orb.local
```

Save and exit (Ctrl+X, Y, Enter)

### 2. Restart Docker containers (if needed)

```bash
cd /Users/lindonfewster/local/gangster-legends
docker-compose restart
```

### 3. Clear Laravel cache

```bash
docker-compose exec -w /var/www/html/laravel-api web php artisan config:clear
docker-compose exec -w /var/www/html/laravel-api web php artisan cache:clear
```

### 4. Rebuild frontend assets

```bash
docker-compose exec -w /var/www/html/laravel-api web npm run build
```

## Access URLs

- **Player Game**: http://web.gangster-legends.orb.local
- **Admin Panel**: http://admin.gangster-legends.orb.local

## Benefits

- Complete separation of admin and player interfaces
- Better security (can add separate firewall rules)
- Cleaner URL structure
- Can configure different session/cookie domains if needed

## Reverting

To revert to using `/admin` path instead of subdomain:

1. Remove `ADMIN_DOMAIN` from `.env`
2. Change AppLayout href back to `/admin`
3. Rebuild frontend assets
