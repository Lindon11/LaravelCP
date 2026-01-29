# 🎮 Gangster Legends - Modern Game Engine

A modern, Laravel-based gangster/mafia PBBG (Persistent Browser-Based Game) engine with a powerful module system.

## ✨ Features

- 🎯 Crime & Training Systems  
- 💪 Combat & Gang Management  
- 💰 Economy & Banking  
- 🧩 **Dynamic Module System**  
- 🎨 Theme Management  
- 🛡️ Admin Panel (Filament)

## 🚀 Quick Start

```bash
cd laravel-api
composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

Visit **http://localhost:8000**

## 🧩 Module Manager

Access: **Admin Panel → System → Module Manager**

Install/uninstall features dynamically. See [MODULE_MANAGER.md](MODULE_MANAGER.md)

## 📖 Documentation

- [Module Manager](MODULE_MANAGER.md)
- [Quick Start](QUICKSTART.md)
- [API Docs](API_IMPLEMENTATION.md)

## 📝 License

MIT License - Open source for game managers

 Star if useful!
