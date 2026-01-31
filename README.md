# Gangster Legends - Full Stack Game Platform

A modern browser-based multiplayer gangster RPG game built with Laravel and Vue 3.

## Architecture Overview

This project consists of two main applications:

### 🎮 **LaravelCP** (Backend + Admin Panel)
- **Tech Stack:** Laravel 11, PHP 8.3, MySQL 8.0, Redis
- **Purpose:** RESTful API backend, authentication, database, game logic
- **Admin Panel:** Built-in Vue 3 admin interface for game management
- **Port:** 8001 (development)
- **Location:** `/LaravelCP`

### 🖥️ **OpenPBBG** (Frontend)
- **Tech Stack:** Vue 3, Vite, Pinia, Vue Router
- **Purpose:** Player-facing game interface
- **Port:** 5175 (development)
- **Location:** `/OpenPBBG`

### Communication Flow
```
┌─────────────────┐         HTTPS/API         ┌──────────────────┐
│                 │◄──────────────────────────►│                  │
│   OpenPBBG      │   Sanctum Auth Tokens      │    LaravelCP     │
│   (Frontend)    │   JSON Responses           │    (Backend)     │
│                 │                             │                  │
│  Vue 3 + Vite   │                             │  Laravel 11 API  │
│  Port: 5175     │                             │  Port: 8001      │
└─────────────────┘                             └──────────────────┘
                                                         │
                                                         │
                                                         ▼
                                                  ┌─────────────┐
                                                  │   MySQL 8   │
                                                  │   + Redis   │
                                                  └─────────────┘
```

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose (recommended)
- OR: PHP 8.3+, Node.js 20+, MySQL 8.0+, Redis, Composer

### 1. Clone Repositories
```bash
# Create project directory
mkdir gangster-legends
cd gangster-legends

# Clone backend
git clone https://github.com/Lindon11/LaravelCP.git

# Clone frontend
# Note: OpenPBBG repository URL will be provided separately
# git clone https://github.com/YourOrg/OpenPBBG.git
```

### 2. Start LaravelCP Backend
```bash
cd LaravelCP
cp .env.example .env
docker-compose up -d

# Install dependencies and setup database
docker exec laravel_app composer install
docker exec laravel_app php artisan key:generate
docker exec laravel_app php artisan migrate --seed

# Build admin panel
docker exec laravel_app bash -c "cd resources/admin && npm install && npm run build"
```

**Access Points:**
- API: http://localhost:8001/api
- Admin Panel: http://localhost:8001/admin
- Admin Login: `admin@admin.com` / `password`

### 3. Start OpenPBBG Frontend
```bash
cd ../OpenPBBG
cp .env.example .env
npm install
npm run dev
```

**Access:**
- Game: http://localhost:5175

## 📚 Documentation

### Detailed Setup & Deployment Guides
- **[LaravelCP Deployment Guide](LaravelCP/LaravelCP-deployment.md)** - Backend setup, production deployment, admin panel
- **[OpenPBBG Deployment Guide](OpenPBBG/OpenPBBG-deployment.md)** - Frontend setup, building, and deployment

### Key Features

#### LaravelCP Backend
- ✅ RESTful API with Laravel 11
- ✅ Sanctum authentication (token-based)
- ✅ Role-based access control (admin, moderator, staff, user, super_admin)
- ✅ Complete game modules:
  - User Management
  - Crimes System
  - Organized Crimes
  - Drugs Trading
  - Items & Inventory
  - Properties & Real Estate
  - Cars & Garage
  - Bounties
  - Gangs
  - Locations & Travel
  - Ranks & Progression
  - Announcements
  - FAQ
  - Support Tickets
- ✅ Built-in Vue 3 Admin Panel
- ✅ Database seeding for quick setup
- ✅ API rate limiting and security

#### OpenPBBG Frontend
- ✅ Modern Vue 3 SPA
- ✅ Responsive design (mobile-friendly)
- ✅ Pinia state management
- ✅ Real-time updates
- ✅ Smooth animations and transitions
- ✅ Player dashboard
- ✅ Crime commission interface
- ✅ Drug trading system
- ✅ Gang management
- ✅ Bounty board
- ✅ Chat system

## 🔐 Default Credentials

### Admin Panel
```
Email: admin@admin.com
Password: password
Role: admin
```

**⚠️ Change immediately in production!**

### Test Users (seeded)
See `LaravelCP/database/seeders/` for additional test accounts.

## 🏗️ Project Structure

```
gangster-legends/
├── LaravelCP/                  # Backend API + Admin Panel
│   ├── app/
│   │   ├── Models/            # Database models
│   │   ├── Http/Controllers/  # API & Admin controllers
│   │   └── Modules/           # Game module logic
│   ├── database/
│   │   ├── migrations/        # Database schema
│   │   └── seeders/           # Initial data
│   ├── resources/
│   │   └── admin/             # Vue 3 admin panel source
│   ├── public/
│   │   └── admin/             # Built admin panel (generated)
│   ├── routes/
│   │   ├── api.php            # API routes
│   │   └── web.php            # Web routes
│   ├── docker-compose.yml     # Docker services
│   ├── Dockerfile             # PHP container config
    └── LaravelCP-deployment.md # Backend deployment guide
│
└── OpenPBBG/                   # Frontend SPA
    ├── src/
    │   ├── components/        # Vue components
    │   ├── views/             # Page views
    │   ├── router/            # Vue Router config
    │   ├── stores/            # Pinia stores
    │   └── composables/       # Reusable logic
    ├── public/                # Static assets
    ├── dist/                  # Built files (generated)
    ├── vite.config.js         # Vite configuration
    └── OpenPBBG-deployment.md # Frontend deployment guide
```

## 🛠️ Development Workflow

### Backend Development (LaravelCP)
```bash
cd LaravelCP

# Start containers
docker-compose up -d

# Run migrations
docker exec laravel_app php artisan migrate

# Seed database
docker exec laravel_app php artisan db:seed

# Create new migration
docker exec laravel_app php artisan make:migration create_something_table

# Create new controller
docker exec laravel_app php artisan make:controller Api/SomethingController

# Run tests
docker exec laravel_app php artisan test

# Check logs
docker logs laravel_app -f
```

### Frontend Development (OpenPBBG)
```bash
cd OpenPBBG

# Start dev server with HMR
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Lint code
npm run lint
```

### Admin Panel Development (LaravelCP)
```bash
cd LaravelCP/resources/admin

# Start dev server
npm run dev

# Build for production
npm run build

# Watch mode (auto-rebuild)
npm run watch
```

## 📦 Tech Stack Details

### Backend (LaravelCP)
- **Framework:** Laravel 11.48.0
- **PHP:** 8.3
- **Database:** MySQL 8.0
- **Cache:** Redis Alpine
- **Auth:** Laravel Sanctum
- **Permissions:** Spatie Laravel Permission 6.24
- **API:** RESTful JSON API
- **Admin Panel:** Vue 3 + Vite 5

### Frontend (OpenPBBG)
- **Framework:** Vue 3.5.27
- **Build Tool:** Vite 7.3.1
- **State Management:** Pinia
- **Routing:** Vue Router 4
- **HTTP Client:** Axios
- **Styling:** Tailwind CSS (if configured)

## 🌐 Production Deployment

### Quick Production Checklist

**LaravelCP:**
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure production database credentials
- [ ] Set strong database passwords
- [ ] Configure `SANCTUM_STATEFUL_DOMAINS` with production domains
- [ ] Set `SESSION_SECURE_COOKIE=true`
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `php artisan config:cache`
- [ ] Setup Nginx/Apache with SSL
- [ ] Configure supervisor for queue workers
- [ ] Setup cron for Laravel scheduler
- [ ] Change default admin password

**OpenPBBG:**
- [ ] Update `.env` with production API URL
- [ ] Run `npm run build`
- [ ] Deploy `dist/` folder to web server
- [ ] Configure Nginx/Apache
- [ ] Install SSL certificate
- [ ] Test CORS configuration

See detailed guides:
- [LaravelCP Production Deployment](LaravelCP/LaravelCP-deployment.md#production-deployment)
- [OpenPBBG Production Deployment](OpenPBBG/OpenPBBG-deployment.md#production-deployment)

## 🔧 Environment Variables

### LaravelCP `.env`
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

SANCTUM_STATEFUL_DOMAINS=localhost:8001,localhost:5175
SESSION_DOMAIN=localhost
```

### OpenPBBG `.env`
```env
VITE_API_URL=http://localhost:8001
VITE_APP_NAME=Gangster Legends
```

## 🐛 Troubleshooting

### Backend Issues
```bash
# Check logs
docker logs laravel_app -f
tail -f LaravelCP/storage/logs/laravel.log

# Clear cache
docker exec laravel_app php artisan config:clear
docker exec laravel_app php artisan cache:clear
docker exec laravel_app php artisan view:clear

# Reset database
docker exec laravel_app php artisan migrate:fresh --seed
```

### Frontend Issues
```bash
# Clear cache and rebuild
cd OpenPBBG
rm -rf node_modules dist
npm install
npm run build

# Check API connectivity
curl http://localhost:8001/api/health
```

### CORS Issues
- Ensure `SANCTUM_STATEFUL_DOMAINS` in LaravelCP includes OpenPBBG domain
- Check browser console for specific error messages
- Verify both apps use same protocol (http/https)

## 📊 Database Schema

The application includes 71 database tables:

**Core Tables:**
- `users` - Player accounts
- `roles`, `permissions` - Access control
- `crimes`, `organized_crimes` - Crime system
- `drugs` - Drug trading
- `items`, `inventories` - Items and player inventory
- `properties` - Real estate
- `cars`, `garages` - Vehicle system
- `bounties` - Bounty system
- `gangs`, `gang_members` - Gang system
- `locations` - Travel locations
- `ranks` - Player progression
- `announcements` - News/updates
- `tickets` - Support system
- `faqs`, `faq_categories` - Help system

See `LaravelCP/database/migrations/` for complete schema.

## 🤝 Contributing

### LaravelCP (Backend)
1. Fork [LaravelCP repository](https://github.com/Lindon11/LaravelCP)
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

### OpenPBBG (Frontend)
Contact repository maintainer for contribution guidelines.

## 📝 License

This project is proprietary. All rights reserved.

## 🆘 Support

- **Issues:** [GitHub Issues](https://github.com/Lindon11/LaravelCP/issues)
- **Documentation:** See deployment guides in each project folder
- **Laravel Docs:** https://laravel.com/docs
- **Vue 3 Docs:** https://vuejs.org/guide/

## 🔄 Version History

- **v1.0.0** (January 2026)
  - Initial release
  - Complete backend API
  - Admin panel with all modules
  - Frontend game interface
  - Full CRUD for all game content

---

**Built with ❤️ using Laravel & Vue 3**

Last Updated: January 31, 2026
