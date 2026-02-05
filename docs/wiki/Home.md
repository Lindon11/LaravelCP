# LaravelCP Documentation

Welcome to the official **LaravelCP** documentation! LaravelCP is a modern, modular Laravel 11 control panel designed for browser-based persistent world games (PBBG/RPG).

---

## 📚 Table of Contents

### Getting Started

- [Installation Guide](Installation-Guide) - Multiple installation methods
- [Configuration](Configuration) - Environment and config files
- [Docker Setup](Docker-Setup) - Container deployment
- [Production Deployment](Production-Deployment) - Server setup guide

### Architecture

- [Project Structure](Project-Structure) - Directory layout explained
- [Core System](Core-System) - Essential components
- [Plugin System](Plugin-System) - Plugin architecture overview
- [Hook System](Hook-System) - Inter-plugin communication

### Development

- [Creating Plugins](Creating-Plugins) - Complete plugin tutorial
- [Routes & Controllers](Routes-and-Controllers) - API development
- [Models & Database](Models-and-Database) - Data layer guide
- [Services](Services) - Business logic layer
- [Testing Guide](Testing-Guide) - PHPUnit testing

### API Reference

- [Authentication API](Authentication-API) - Auth endpoints
- [Admin API](Admin-API) - Admin panel endpoints
- [Plugin API](Plugin-API) - All plugin endpoints

### Admin Panel

- [Admin Panel Guide](Admin-Panel-Guide) - Using the admin panel

### Security & Best Practices

- [Security Best Practices](Security-Best-Practices) - Security guidelines

### Reference

- [Environment Variables](Environment-Variables) - Complete .env reference
- [Troubleshooting](Troubleshooting) - Common issues and solutions

---

## 🚀 Quick Start

```bash
# Clone repository
git clone https://github.com/Lindon11/LaravelCP.git
cd LaravelCP

# Using Docker (recommended)
docker compose up -d
docker compose exec app composer install
cp .env.example .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app bash -c "cd resources/admin && npm install && npm run build"

# Access application
# API: http://localhost:8001
# Admin Panel: http://localhost:8001/admin
```

**Default Admin Credentials:**

- Username: `admin`
- Password: `admin123`
- ⚠️ You must change password on first login

---

## 🏗️ Architecture Overview

```text
LaravelCP/
├── app/
│   ├── Core/           # Essential system components
│   ├── Plugins/        # Game feature plugins (28 built-in)
│   ├── Facades/        # Laravel facades
│   └── Console/        # Artisan commands
├── config/             # Configuration files
├── database/           # Migrations & seeders
├── resources/admin/    # Vue.js admin panel
├── routes/             # API & web routes
└── themes/             # Frontend themes
```

---

## 📦 Built-in Plugins

LaravelCP includes 28 game feature plugins:

| Category | Plugins |
| ---------- | --------- |
| **Actions** | Crimes, Organized Crime, Theft, Drugs |
| **Combat** | Combat (PvE/NPC), Bounty, Jail, Hospital |
| **Economy** | Bank, Market, Properties, Employment, Stocks |
| **Social** | Chat, Forum, Gang, Messaging, Alliances |
| **Progression** | Education, Gym, Missions, Achievements, Quests |
| **Entertainment** | Casino, Racing, Lottery, Tournament |
| **System** | Announcements, Tickets, Wiki, Daily Rewards |

---

## 🔗 Useful Links

- **GitHub Repository**: [github.com/Lindon11/LaravelCP](https://github.com/Lindon11/LaravelCP)
- **API Documentation**: `/docs` (after installation)
- **Issues**: [GitHub Issues](https://github.com/Lindon11/LaravelCP/issues)

---

## 📄 License

LaravelCP is open-source software licensed under the MIT license.
