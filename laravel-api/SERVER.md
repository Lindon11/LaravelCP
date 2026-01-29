# Laravel Server Management

## Persistent Server (Production-like)

The Laravel server now runs persistently in the background using nohup.

### Start Server
```bash
/var/www/html/laravel-api/start-server.sh
```

### Check Server Status
```bash
ps aux | grep "php artisan serve" | grep -v grep
```

### View Server Logs
```bash
tail -f /var/www/html/laravel-api/storage/logs/server.log
```

### Stop Server
```bash
pkill -f "php artisan serve"
```

### Restart Server
```bash
pkill -f "php artisan serve" && /var/www/html/laravel-api/start-server.sh
```

## Server URL
- **Game**: http://web.gangster-legends.orb.local:8000
- **Admin**: http://web.gangster-legends.orb.local:8000/admin

## Notes
- Server runs in background and survives terminal commands
- Logs are written to `storage/logs/server.log`
- Server auto-restarts if script is run while already running
