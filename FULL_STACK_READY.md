# 🎮 Gangster Legends - Full Stack Setup Complete!

## ✅ What's Running

### Backend API (Laravel)
- **URL:** http://localhost:8000
- **API:** http://localhost:8000/api
- **Admin:** http://localhost:8000/admin
- **Status:** ✅ Running

### Frontend (Vue 3)
- **URL:** http://localhost:3000
- **Status:** ✅ Running
- **Framework:** Vue 3 + Vite + Pinia

## 📱 Access the Application

1. **Frontend:** Open http://localhost:3000
   - Login page with username/email and password
   - Register new account
   - Dashboard with player stats and game features

2. **Admin Panel:** Open http://localhost:8000/admin
   - Manage all game content
   - View players, ranks, locations
   - 40+ resource managers

3. **API:** http://localhost:8000/api
   - RESTful JSON API
   - Token authentication
   - See API.md for documentation

## 🎯 Next Steps

### 1. Create Your Account
- Go to http://localhost:3000
- Click "Register"
- Create your gangster character

### 2. Play the Game
- Login to your account
- Access dashboard with:
  - Player stats (health, energy, cash)
  - 23 game modules (Crimes, Gym, Hospital, etc.)
  - Rank progression
  - Travel between cities

### 3. Admin Access
- Go to http://localhost:8000/admin
- Login with admin credentials from installation
- Manage game content

## 🛠️ Development

### Backend Commands
```bash
cd laravel-api

# Clear caches
docker-compose exec -w /var/www/html/laravel-api web php artisan cache:clear

# View routes
docker-compose exec -w /var/www/html/laravel-api web php artisan route:list

# Run migrations
docker-compose exec -w /var/www/html/laravel-api web php artisan migrate
```

### Frontend Commands
```bash
cd frontend-app

# Install dependencies
docker-compose exec -w /var/www/html/frontend-app web npm install

# Start dev server
docker-compose exec -w /var/www/html/frontend-app web npm run dev

# Build for production
docker-compose exec -w /var/www/html/frontend-app web npm run build
```

## 📁 Project Structure

```
gangster-legends/
├── laravel-api/              # Backend API
│   ├── app/
│   │   ├── AdminDashboard/  # Filament admin resources
│   │   ├── Http/Controllers/
│   │   │   └── Api/         # API controllers
│   │   ├── Models/          # Database models
│   │   ├── Modules/         # 23 game modules
│   │   └── Services/        # Business logic
│   ├── routes/
│   │   ├── api.php          # API routes
│   │   └── web.php          # Admin/installer only
│   └── database/
│
├── frontend-app/             # Vue 3 Frontend
│   ├── src/
│   │   ├── views/           # Login, Register, Dashboard
│   │   ├── stores/          # Pinia auth store
│   │   ├── services/        # API client
│   │   └── router/          # Vue Router
│   └── .env                 # API URL config
│
└── docker-compose.yml        # Docker setup
```

## 🔧 Configuration

### Backend (.env)
Located at: `laravel-api/.env`
```env
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=gangster_legends
```

### Frontend (.env)
Located at: `frontend-app/.env`
```env
VITE_API_URL=http://localhost:8000/api
```

## 🎮 Game Features

All accessible from http://localhost:3000/dashboard:

1. **Core Features:**
   - Crimes - Commit crimes for cash
   - Gym - Train your stats
   - Hospital - Heal your health
   - Jail - Bust out players

2. **Economy:**
   - Bank - Deposit/withdraw money
   - Shop - Buy items
   - Inventory - Manage items

3. **Combat:**
   - Attack - Fight other players
   - Bounties - Place hits
   - Detective - Search players

4. **Activities:**
   - Theft - Steal cars
   - Racing - Race for cash
   - Drugs - Drug dealing
   - Travel - Visit cities

5. **Social:**
   - Gangs - Join/create gangs
   - Forum - Community discussions
   - Organized Crime - Team heists

6. **Progress:**
   - Achievements - Track progress
   - Missions - Complete quests
   - Leaderboards - Top players

## 📚 Documentation

- **API Documentation:** `laravel-api/API.md`
- **Conversion Guide:** `laravel-api/CONVERSION_COMPLETE.md`
- **Backend README:** `laravel-api/README.md`
- **Frontend README:** `frontend-app/README.md`

## 🚀 Production Deployment

### Backend
```bash
cd laravel-api
composer install --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Frontend
```bash
cd frontend-app
npm run build
# Deploy dist/ folder to web server
```

## 🔑 API Authentication

The frontend automatically handles authentication:
1. Login/Register gets a token
2. Token stored in localStorage
3. Automatically added to API requests
4. Auto-redirect on 401 errors

## 🎨 Tech Stack

**Backend:**
- Laravel 11
- MySQL/MariaDB
- Sanctum (API auth)
- Filament 3 (Admin)
- Spatie Permissions

**Frontend:**
- Vue 3
- Vite
- Pinia (State)
- Vue Router
- Axios
- Tailwind CSS

## 🐛 Troubleshooting

**Frontend can't connect to API:**
- Check `.env` has correct API URL
- Ensure backend is running on port 8000
- Check CORS settings in `laravel-api/config/cors.php`

**401 Unauthorized:**
- Clear localStorage
- Re-login to get new token
- Check token is being sent in headers

**Module not showing:**
- Check module is enabled in database
- Verify API endpoint is working
- Check browser console for errors

## 🎉 You're Ready!

Your full-stack Gangster Legends game is now running with:
- ✅ API Backend
- ✅ Vue Frontend  
- ✅ Admin Dashboard
- ✅ 23 Game Modules
- ✅ Authentication System
- ✅ Rank Progression
- ✅ Location Travel

**Start playing:** http://localhost:3000
