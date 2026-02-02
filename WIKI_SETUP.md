# Setting Up GitHub Wiki for LaravelCP

## Enabling GitHub Wiki

1. Go to your repository: https://github.com/Lindon11/LaravelCP
2. Click on **Settings** tab
3. Scroll down to the **Features** section
4. Check the **Wikis** checkbox
5. Click **Save**

## Creating Wiki Pages

Once enabled, you can access the wiki at:
https://github.com/Lindon11/LaravelCP/wiki

## Suggested Wiki Structure

### Home Page
```markdown
# Welcome to LaravelCP Wiki

LaravelCP is a modular PHP-based browser game framework inspired by games like TornCity. 
This wiki contains comprehensive documentation for developers and administrators.

## Quick Links
- [Installation Guide](Installation)
- [Module Development](Module-Development)
- [API Documentation](API-Documentation)
- [Admin Panel Guide](Admin-Panel)
- [Database Schema](Database-Schema)

## Getting Started
- [Quick Start Guide](Quick-Start)
- [System Requirements](Requirements)
- [Configuration](Configuration)

## Development
- [Creating Modules](Creating-Modules)
- [Hook System](Hook-System)
- [Frontend Development](Frontend-Development)
- [Testing](Testing)

## Examples
- [Basic Module Example](Example-Basic-Module)
- [Advanced Module Example](Example-Advanced-Module)
- [Custom Admin Panel](Example-Admin-Panel)
```

### Installation Page
```markdown
# Installation Guide

## Requirements
- PHP 8.2 or higher
- MySQL 8.0 or higher
- Node.js 18+ and npm
- Composer
- Docker (optional, recommended)

## Installation Steps

### 1. Clone Repository
bash
git clone https://github.com/Lindon11/LaravelCP.git
cd LaravelCP


### 2. Install Dependencies
bash
composer install
npm install


### 3. Environment Configuration
bash
cp .env.example .env
php artisan key:generate


### 4. Database Setup
bash
php artisan migrate
php artisan db:seed


### 5. Admin User
bash
php artisan tinker
User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'is_admin' => true
]);


### 6. Start Development Server
bash
php artisan serve
# Frontend (separate terminal)
cd ../OpenPBBG && npm run dev


## Docker Installation

bash
docker-compose up -d
docker exec laravel_app php artisan migrate
docker exec laravel_app php artisan db:seed


Visit: http://localhost:8001
```

### Module Development Page
```markdown
# Module Development Guide

For complete module development documentation, see:
[MODULE_DEVELOPMENT.md](https://github.com/Lindon11/LaravelCP/blob/main/MODULE_DEVELOPMENT.md)

## Quick Start

### 1. Create Module Structure
bash
mkdir -p app/Modules/MyModule/Controllers
mkdir -p app/Modules/MyModule/routes


### 2. Create Main Module Class
php
<?php
namespace App\Modules\MyModule;
use App\Modules\Module;

class MyModuleModule extends Module
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }
}


### 3. Create module.json
json
{
    "name": "mymodule",
    "display_name": "My Module",
    "description": "Module description",
    "version": "1.0.0",
    "icon": "🎮"
}


### 4. Register in Database
sql
INSERT INTO modules (name, display_name, enabled, required_level)
VALUES ('mymodule', 'My Module', 1, 1);


## Examples
- [Simple Module](Example-Simple-Module)
- [Database Module](Example-Database-Module)
- [Complex Module](Example-Complex-Module)
```

### API Documentation Page
```markdown
# API Documentation

## Authentication
All API requests require authentication using Laravel Sanctum tokens.

http
Authorization: Bearer YOUR_TOKEN_HERE


## Endpoints

### Player Stats
GET /api/player/stats


Response:
json
{
  "id": 1,
  "name": "Player",
  "level": 5,
  "cash": 10000,
  "bank": 50000,
  "energy": 75,
  "health": 100
}


### Modules

#### Get Available Modules
GET /api/modules


#### Get Module Data
GET /api/{module-name}


### Common Response Format
json
{
  "success": true,
  "message": "Action completed",
  "data": {}
}


## Error Handling

### 400 Bad Request
json
{
  "success": false,
  "message": "Invalid input"
}


### 429 Too Many Requests
json
{
  "success": false,
  "message": "Please wait before trying again",
  "cooldown": 45
}


### 401 Unauthorized
json
{
  "message": "Unauthenticated."
}

```

### Database Schema Page
```markdown
# Database Schema

## Core Tables

### users
- `id` - Primary key
- `name` - Username
- `email` - Email address
- `password` - Hashed password
- `level` - Player level
- `experience` - Current XP
- `cash` - Money on hand
- `bank` - Money in bank
- `energy` - Current energy (0-100)
- `health` - Current health (0-100)
- `rank_id` - Foreign key to ranks
- `location_id` - Foreign key to locations
- `gang_id` - Foreign key to gangs (nullable)

### modules
- `id` - Primary key
- `name` - Unique module identifier
- `display_name` - Display name
- `description` - Module description
- `icon` - Emoji icon
- `enabled` - Boolean active status
- `required_level` - Minimum level required
- `order` - Display order

### user_timers
- `id` - Primary key
- `user_id` - Foreign key to users
- `type` - Timer type (crime, travel, etc.)
- `expires_at` - Timestamp when timer expires

## Module-Specific Tables

### crimes
- `id` - Primary key
- `name` - Crime name
- `description` - Crime description
- `energy_cost` - Energy required
- `cooldown` - Seconds before next attempt
- `min_reward` - Minimum cash reward
- `max_reward` - Maximum cash reward
- `required_level` - Level requirement

### crime_attempts
- `id` - Primary key
- `user_id` - Foreign key to users
- `crime_id` - Foreign key to crimes
- `success` - Boolean success status
- `reward` - Cash earned
- `attempted_at` - Timestamp

[More tables documented in DATABASE_SCHEMA.md]
```

## Wiki Navigation Tips

1. **Use Sidebar**: Add page links to `_Sidebar.md` for easy navigation
2. **Link Between Pages**: Use `[[Page Name]]` to link to other wiki pages
3. **Add Images**: Upload images and reference with `![Alt text](image-url)`
4. **Code Blocks**: Use triple backticks with language for syntax highlighting
5. **Table of Contents**: Use `[[_TOC_]]` for automatic TOC generation

## Best Practices

1. **Keep Pages Focused**: One topic per page
2. **Use Examples**: Include code examples for clarity
3. **Keep Updated**: Update wiki when code changes
4. **Cross-Reference**: Link related pages together
5. **Include Diagrams**: Add architecture diagrams where helpful

## Converting Existing Documentation

You can convert the existing .md files to wiki pages:
- `MODULE_DEVELOPMENT.md` → "Module Development" wiki page
- `API.md` → "API Documentation" wiki page
- `INSTALLATION.md` → "Installation Guide" wiki page
- `MODULE_HOOK_SYSTEM.md` → "Hook System" wiki page

## Cloning Wiki for Local Editing

The wiki can be cloned as a git repository:

```bash
git clone https://github.com/Lindon11/LaravelCP.wiki.git
cd LaravelCP.wiki
# Edit markdown files
git add .
git commit -m "Update documentation"
git push
```

## Additional Features

- **Search**: Wiki has built-in search functionality
- **History**: Track changes to wiki pages
- **Discussions**: Enable Discussions for Q&A
- **Issues**: Link wiki pages to GitHub issues
- **Projects**: Organize development tasks

---

Visit: https://github.com/Lindon11/LaravelCP/wiki (after enabling)
