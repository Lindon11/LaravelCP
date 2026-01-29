# Criminal Empire - Laravel 11 Docker Stack

## 🐳 **Complete Docker Setup - No Local Dependencies!**

Everything runs in containers. No need to install PHP, Composer, Node.js, or MySQL locally!

---

## 📦 **What's Included**

### **Services (9 Containers)**
1. **laravel** - Main Laravel 11 application (port 8000)
2. **websocket** - Laravel Websockets server (port 6001)
3. **queue** - Background job worker
4. **scheduler** - Laravel task scheduler (cron)
5. **db** - MariaDB database (port 3306)
6. **redis** - Cache & session store (port 6379)
7. **phpmyadmin** - Database GUI (port 8081)
8. **mailpit** - Email testing (ports 1025, 8025)
9. **frontend** - Node.js + Vite for hot reload (port 5173)

### **Features**
✅ **Laravel 11** with Jetstream (auth + teams)  
✅ **Laravel Websockets** (replaces Ratchet)  
✅ **Filament 3** admin panel  
✅ **Spatie Permissions** for roles/gangs  
✅ **Vue 3 + Inertia.js** modern frontend  
✅ **Redis** for caching and sessions  
✅ **Queue workers** for background jobs  
✅ **Automatic scheduler** for cron tasks  
✅ **Hot reload** with Vite  

---

## 🚀 **Quick Start**

### **Step 1: Install (One Command)**

```bash
cd /var/www/html
bash install-laravel-docker.sh
```

This will:
- Create Laravel 11 project
- Install all packages (Jetstream, Websockets, Filament, etc.)
- Build Docker containers
- Configure everything automatically

⏱️ **Time: ~10-15 minutes** (depends on internet speed)

---

### **Step 2: Start Services**

```bash
docker-compose -f docker-compose-laravel.yml up -d
```

Or use the helper script:

```bash
bash start-docker.sh
```

---

### **Step 3: Run Migrations**

```bash
docker-compose -f docker-compose-laravel.yml exec laravel php artisan migrate
```

---

### **Step 4: Install Frontend**

```bash
# Install npm packages
docker-compose -f docker-compose-laravel.yml exec laravel npm install

# Build assets
docker-compose -f docker-compose-laravel.yml exec laravel npm run build
```

---

### **Step 5: Create Admin User**

```bash
docker-compose -f docker-compose-laravel.yml exec laravel php artisan make:filament-user
```

---

### **Step 6: Access Your App**

- 🌐 **Laravel:** http://web.gangster-legends.orb.local:8000
- 👤 **Admin Panel:** http://web.gangster-legends.orb.local:8000/admin
- 🔌 **WebSocket Debug:** http://web.gangster-legends.orb.local:8000/laravel-websockets
- 💾 **phpMyAdmin:** http://web.gangster-legends.orb.local:8081
- 📧 **Mailpit:** http://web.gangster-legends.orb.local:8025

---

## 🛠️ **Common Commands**

### **Container Management**

```bash
# Start all services
docker-compose -f docker-compose-laravel.yml up -d

# Stop all services
docker-compose -f docker-compose-laravel.yml down

# View logs (all services)
docker-compose -f docker-compose-laravel.yml logs -f

# View logs (specific service)
docker-compose -f docker-compose-laravel.yml logs -f laravel

# Restart a service
docker-compose -f docker-compose-laravel.yml restart laravel

# Rebuild containers (after Dockerfile changes)
docker-compose -f docker-compose-laravel.yml up -d --build
```

---

### **Laravel Artisan Commands**

```bash
# Run any artisan command
docker-compose -f docker-compose-laravel.yml exec laravel php artisan [command]

# Examples:
docker-compose -f docker-compose-laravel.yml exec laravel php artisan migrate
docker-compose -f docker-compose-laravel.yml exec laravel php artisan db:seed
docker-compose -f docker-compose-laravel.yml exec laravel php artisan cache:clear
docker-compose -f docker-compose-laravel.yml exec laravel php artisan queue:work
docker-compose -f docker-compose-laravel.yml exec laravel php artisan tinker
```

---

### **Database Access**

```bash
# MySQL CLI
docker-compose -f docker-compose-laravel.yml exec db mysql -u dev -pdev gangster_legends

# Or use phpMyAdmin at http://localhost:8081
# Username: dev
# Password: dev
```

---

### **Shell Access**

```bash
# Access Laravel container bash
docker-compose -f docker-compose-laravel.yml exec laravel bash

# Access database container
docker-compose -f docker-compose-laravel.yml exec db bash

# Access Redis CLI
docker-compose -f docker-compose-laravel.yml exec redis redis-cli
```

---

### **Composer Commands**

```bash
# Install package
docker-compose -f docker-compose-laravel.yml exec laravel composer require vendor/package

# Update dependencies
docker-compose -f docker-compose-laravel.yml exec laravel composer update

# Dump autoload
docker-compose -f docker-compose-laravel.yml exec laravel composer dump-autoload
```

---

### **NPM Commands**

```bash
# Install packages
docker-compose -f docker-compose-laravel.yml exec laravel npm install

# Run dev build
docker-compose -f docker-compose-laravel.yml exec laravel npm run dev

# Production build
docker-compose -f docker-compose-laravel.yml exec laravel npm run build

# Watch for changes
docker-compose -f docker-compose-laravel.yml exec frontend npm run dev
```

---

## 🗄️ **Database Configuration**

### **Connection Details**

```env
DB_HOST=db              # Container name
DB_PORT=3306
DB_DATABASE=gangster_legends
DB_USERNAME=dev
DB_PASSWORD=dev
```

### **From Host Machine**

```bash
mysql -h 127.0.0.1 -P 3306 -u dev -pdev gangster_legends
```

Or use phpMyAdmin: http://web.gangster-legends.orb.local:8081

---

## 🔌 **WebSocket Setup**

### **Server Side (Already Running)**

WebSocket server runs automatically on port 6001 in the `websocket` container.

### **Frontend Integration**

```javascript
// In your Vue components or JavaScript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'local',
    wsHost: window.location.hostname,
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
});

// Subscribe to channel
Echo.channel('chat.general')
    .listen('ChatMessageSent', (e) => {
        console.log('New message:', e.message);
    });
```

### **Debug WebSocket**

Visit: http://web.gangster-legends.orb.local:8000/laravel-websockets

---

## 📧 **Email Testing (Mailpit)**

All emails sent by Laravel are caught by Mailpit:

- **Web UI:** http://web.gangster-legends.orb.local:8025
- **SMTP:** web.gangster-legends.orb.local:1025

No emails actually sent during development!

---

## 🔄 **Integrating with Legacy Code**

### **Option 1: Shared Database**

Both systems use the same database:

```php
// In Laravel, create model for existing table
class User extends Model {
    protected $table = 'users';  // Your existing table
}
```

### **Option 2: API Wrapper**

Create Laravel API that calls legacy code:

```php
// routes/api.php
Route::post('/chat/send', function(Request $request) {
    // Call legacy chat module
    require_once __DIR__ . '/../../modules/installed/chat/chat.inc.php';
    
    // Or use Laravel logic
    return response()->json(['success' => true]);
});
```

### **Option 3: Gradual Migration**

1. Keep legacy code running on port 8080
2. Run Laravel on port 8000
3. Migrate modules one at a time
4. Use reverse proxy to combine

---

## 🚨 **Troubleshooting**

### **Port Already in Use**

```bash
# Check what's using port 8000
lsof -i :8000
# Or
netstat -tulpn | grep 8000

# Kill the process
kill -9 [PID]
```

### **Database Connection Failed**

```bash
# Check if database is running
docker-compose -f docker-compose-laravel.yml ps db

# Restart database
docker-compose -f docker-compose-laravel.yml restart db

# Check logs
docker-compose -f docker-compose-laravel.yml logs db
```

### **Permission Errors**

```bash
# Fix permissions in Laravel container
docker-compose -f docker-compose-laravel.yml exec laravel chown -R www-data:www-data /var/www/html
docker-compose -f docker-compose-laravel.yml exec laravel chmod -R 755 storage bootstrap/cache
```

### **Clear All Caches**

```bash
docker-compose -f docker-compose-laravel.yml exec laravel php artisan optimize:clear
```

### **Reset Everything**

```bash
# Stop and remove all containers
docker-compose -f docker-compose-laravel.yml down

# Remove volumes (⚠️ deletes database data!)
docker-compose -f docker-compose-laravel.yml down -v

# Rebuild from scratch
bash install-laravel-docker.sh
```

---

## 📊 **Resource Usage**

Typical memory usage:
- laravel: ~100MB
- websocket: ~50MB
- db: ~200MB
- redis: ~10MB
- Total: ~500MB

---

## 🎯 **Development Workflow**

### **Daily Workflow**

```bash
# Morning - start services
bash start-docker.sh

# Make changes to code (files are live-mounted)

# Run migrations/seeds
docker-compose -f docker-compose-laravel.yml exec laravel php artisan migrate

# Clear cache after config changes
docker-compose -f docker-compose-laravel.yml exec laravel php artisan config:clear

# End of day - stop services
bash stop-docker.sh
```

### **Code Changes**

- PHP files are **live-mounted** - changes apply immediately
- Frontend changes need rebuild: `npm run build` or `npm run dev`
- Config changes need cache clear: `php artisan config:clear`

---

## 🚀 **Production Deployment**

When ready for production:

1. Update `.env` with production settings
2. Set `APP_ENV=production` and `APP_DEBUG=false`
3. Use proper database credentials
4. Configure SSL/HTTPS
5. Use production web server (nginx/apache)
6. Set up proper backups
7. Configure monitoring/logging

---

## 📚 **Resources**

- **Laravel Docs:** https://laravel.com/docs/11.x
- **Docker Docs:** https://docs.docker.com
- **Jetstream:** https://jetstream.laravel.com
- **Filament:** https://filamentphp.com
- **Laravel Websockets:** https://beyondco.de/docs/laravel-websockets

---

## 💡 **Pro Tips**

1. **Use aliases** for common commands:
   ```bash
   alias dc='docker-compose -f docker-compose-laravel.yml'
   alias dcexec='docker-compose -f docker-compose-laravel.yml exec laravel'
   alias artisan='docker-compose -f docker-compose-laravel.yml exec laravel php artisan'
   ```

2. **Watch logs** in separate terminal:
   ```bash
   docker-compose -f docker-compose-laravel.yml logs -f laravel
   ```

3. **Quick rebuild**:
   ```bash
   docker-compose -f docker-compose-laravel.yml up -d --build --force-recreate
   ```

---

**No PHP, Composer, Node.js, or MySQL installation required on host!** 🎉

Everything runs isolated in Docker containers. Your host system stays clean!
