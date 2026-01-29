# Laravel 11 Gaming Platform Starter

**Complete foundation for transitioning Criminal Empire to modern Laravel stack**

## 🚀 Features Included

✅ **Laravel 11** - Latest PHP framework  
✅ **Jetstream** - Authentication, teams, profile management  
✅ **Laravel Websockets** - Real-time communication (replaces Ratchet)  
✅ **Filament** - Modern admin panel for game management  
✅ **Spatie Permissions** - Roles, permissions, and gang hierarchies  
✅ **Vue 3 + Inertia.js** - Reactive modern UI  
✅ **Sanctum** - API authentication tokens  
✅ **Laravel Echo** - WebSocket client for frontend  
✅ **Monolog** - Advanced logging (already in use)  

---

## 📦 Installation

### Step 1: Create Laravel Project

```bash
cd /var/www/html
composer create-project laravel/laravel laravel-api "11.*"
cd laravel-api
```

### Step 2: Install All Packages

```bash
# Core packages
composer require laravel/jetstream
composer require beyondcode/laravel-websockets
composer require filament/filament:"^3.0"
composer require spatie/laravel-permission
composer require laravel/sanctum

# Development tools
composer require --dev barryvdh/laravel-debugbar
composer require --dev laravel/telescope
```

### Step 3: Install Jetstream with Inertia + Vue

```bash
php artisan jetstream:install inertia --teams
npm install && npm run build
```

### Step 4: Install Filament Admin Panel

```bash
php artisan filament:install --panels
php artisan make:filament-user
```

### Step 5: Configure Database (Shared with Existing System)

Edit `laravel-api/.env`:

```env
# Copy from your existing /var/www/html/config.php
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_existing_database
DB_USERNAME=your_existing_user
DB_PASSWORD=your_existing_password

# WebSocket Configuration
LARAVEL_WEBSOCKETS_HOST=0.0.0.0
LARAVEL_WEBSOCKETS_PORT=6001
BROADCAST_DRIVER=pusher

# Pusher Config (for Laravel Websockets)
PUSHER_APP_ID=local
PUSHER_APP_KEY=local
PUSHER_APP_SECRET=local
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1

# App Settings
APP_NAME="Criminal Empire"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Step 6: Publish and Configure

```bash
# Publish configs
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --tag=jetstream-views
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="config"

# Run migrations (only creates new Laravel tables, won't touch existing)
php artisan migrate
```

### Step 7: Start Services

```bash
# Start Laravel WebSocket server (replaces your current websocket-server.php)
php artisan websockets:serve

# In another terminal, start Laravel dev server
php artisan serve --host=0.0.0.0 --port=8000

# In another terminal, watch for frontend changes
npm run dev
```

---

## 🔗 Integration Strategy (Hybrid Approach)

### Phase 1: Parallel Installation (Week 1)
- ✅ Install Laravel in `/var/www/html/laravel-api/`
- ✅ Connect to existing database (shared)
- ✅ Create Laravel models for existing tables (users, admin_chat, etc.)
- ✅ Test authentication against existing user table

### Phase 2: API Wrapper (Week 2)
- Create API endpoints that wrap existing functionality
- Example: `POST /api/chat/send` calls existing chat module
- Test API with Postman/Insomnia
- Add Sanctum token authentication

### Phase 3: Chat Migration (Weeks 3-4)
- Migrate chat module to Laravel
- Replace Ratchet with Laravel Websockets
- Use Laravel Broadcasting + Echo
- Keep old chat.inc.php as fallback

### Phase 4: Admin Panel (Week 5)
- Build Filament resources for users, gangs, economy
- Replace old admin pages one by one
- Add real-time dashboards

### Phase 5: Frontend Modernization (Weeks 6-8)
- Convert pages to Inertia + Vue one at a time
- Start with high-traffic pages (profile, gang page)
- Use Laravel API for data

---

## 📋 Quick Reference Commands

```bash
# Start all services (add to your startup script)
cd /var/www/html/laravel-api

# WebSocket server (background)
nohup php artisan websockets:serve > /tmp/laravel-websockets.log 2>&1 &

# Queue worker (for jobs)
nohup php artisan queue:work --daemon > /tmp/laravel-queue.log 2>&1 &

# Laravel scheduler (add to crontab)
* * * * * cd /var/www/html/laravel-api && php artisan schedule:run >> /dev/null 2>&1

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate IDE helper (optional but helpful)
composer require --dev barryvdh/laravel-ide-helper
php artisan ide-helper:generate
php artisan ide-helper:models --nowrite
```

---

## 🗄️ Database Integration

### Existing Users Table
Create a Laravel model that uses your existing users table:

```bash
php artisan make:model User
```

Edit `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    // Use your existing table name
    protected $table = 'users';
    
    // Map to your existing columns
    protected $fillable = [
        'username',
        'email', 
        'password',
        'money',
        'points',
        // ... your other columns
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // If your password column uses different hashing, override
    public function getAuthPassword()
    {
        return $this->password;
    }
}
```

---

## 🔧 WebSocket Migration

### Old System (Ratchet)
```php
// Your current websocket-server.php on port 8080/8081
```

### New System (Laravel Websockets)
```bash
# Runs on port 6001 by default
php artisan websockets:serve
```

### Frontend Changes
Replace your current WebSocket client with Laravel Echo:

```javascript
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

// Subscribe to chat channel
Echo.channel('chat.general')
    .listen('ChatMessageSent', (e) => {
        console.log('New message:', e.message);
    });
```

---

## 📊 Filament Admin Resources

### Example: User Management Resource

```bash
php artisan make:filament-resource User --generate
```

This creates a full CRUD interface for users with:
- List view with filters, search, bulk actions
- Create/edit forms
- Relation managers (gangs, transactions, etc.)
- Export to Excel/PDF
- Charts and widgets

### Access Admin Panel
```
http://localhost:8000/admin
```

---

## 🔐 Authentication Flow

### Existing System → Laravel
1. User logs in via existing login page
2. Creates Laravel session
3. Issues Sanctum token for API
4. Can access both old and new features

### Laravel → Existing System
1. Laravel auth uses same users table
2. Session shared (same domain/cookies)
3. Seamless transition

---

## 🎯 Next Steps After Installation

1. **Test Database Connection**
   ```bash
   php artisan tinker
   >>> User::count()
   >>> User::first()
   ```

2. **Create First Admin User**
   ```bash
   php artisan make:filament-user
   # Enter your existing admin email/password
   ```

3. **Test WebSocket**
   - Open `http://localhost:8000/laravel-websockets`
   - Check dashboard shows connections

4. **Create Test API Endpoint**
   ```bash
   php artisan make:controller Api/ChatController
   ```

5. **Test from Existing Chat**
   - Call Laravel API from chat.inc.php
   - Verify data flows both ways

---

## 🚨 Important Notes

### Don't Touch Existing System
- Laravel installs in separate folder
- Uses same database, doesn't modify tables
- Old WebSocket server keeps running during transition
- Zero downtime migration

### Gradual Migration
- Both systems run in parallel
- Migrate one module at a time
- Test thoroughly before switching
- Keep old code as fallback

### Performance
- Laravel is faster than your current custom framework
- WebSocket server handles 10,000+ concurrent connections
- Eloquent ORM with query caching
- Redis/Memcached support built-in

---

## 📚 Learning Resources

- **Laravel Docs**: https://laravel.com/docs/11.x
- **Jetstream**: https://jetstream.laravel.com
- **Filament**: https://filamentphp.com/docs
- **Inertia.js**: https://inertiajs.com
- **Laravel Websockets**: https://beyondco.de/docs/laravel-websockets
- **Spatie Permissions**: https://spatie.be/docs/laravel-permission

---

## 🤝 Support

Need help? Check:
- Laravel Discord: https://discord.gg/laravel
- Laracasts: https://laracasts.com (video tutorials)
- Stack Overflow: Tag with `laravel`

---

**Installation Time**: ~30 minutes  
**Learning Curve**: 1-2 weeks to get comfortable  
**Migration Time**: 6-8 weeks for full transition (incremental)

Ready to start? Run the commands in Step 1! 🚀
