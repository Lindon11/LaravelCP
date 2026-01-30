# Module Manager System

The Module Manager allows you to dynamically install, uninstall, enable, and disable modules and themes without modifying the core codebase.

## Features

- **Install Modules**: Upload and install new modules via ZIP files
- **Install Themes**: Upload and install new themes
- **Enable/Disable**: Toggle modules on/off without uninstalling
- **Dependency Management**: Automatic dependency checking
- **Version Tracking**: Track module versions
- **Auto-Discovery**: Automatically discovers modules in the `/modules` and `/themes` directories

## Database

The `installed_modules` table tracks all installed modules and themes:

- `name`: Display name
- `slug`: Unique identifier
- `version`: Semantic version (e.g., "1.0.0")
- `type`: module, theme, or plugin
- `description`: Module description
- `dependencies`: JSON array of required modules
- `config`: JSON configuration data
- `enabled`: Boolean status
- `installed_at`: Installation timestamp

## Module Structure

### Directory Layout

```
modules/
└── your-module/
    ├── module.json          # Required manifest file
    ├── README.md            # Documentation
    ├── src/                 # PHP classes
    │   └── Installer.php    # Optional install/uninstall hooks
    ├── routes/              # Module routes
    │   └── web.php
    ├── database/            # Module database files
    │   └── migrations/
    ├── views/               # Blade templates
    ├── assets/              # CSS, JS, images (copied to public/)
    └── config/              # Configuration files
```

### module.json Schema

```json
{
  "name": "Module Name",
  "slug": "module-slug",
  "version": "1.0.0",
  "description": "Module description",
  "author": "Author Name",
  "type": "module",
  "dependencies": {
    "required-module": ">=1.0.0"
  },
  "config": {
    "enabled_by_default": true
  },
  "autoload": {
    "psr-4": {
      "Modules\\ModuleName\\": "src/"
    }
  },
  "routes": [
    "routes/web.php"
  ],
  "migrations": [
    "database/migrations"
  ],
  "providers": []
}
```

### Optional Installer Class

Create `src/Installer.php` for custom installation logic:

```php
<?php

namespace Modules\YourModule;

class Installer
{
    public function install(): void
    {
        // Custom installation logic
        // Example: seed data, create files, etc.
    }

    public function uninstall(): void
    {
        // Custom cleanup logic
        // Example: remove files, clear cache, etc.
    }
}
```

## API Endpoints

All routes require `auth:sanctum` and `admin` middleware.

### Modules

- `GET /api/admin/modules` - List all available modules
- `POST /api/admin/modules/upload` - Upload module ZIP
- `POST /api/admin/modules/create` - Create new module structure
- `POST /api/admin/modules/{slug}/install` - Install module
- `DELETE /api/admin/modules/{slug}` - Uninstall module
- `PUT /api/admin/modules/{slug}/enable` - Enable module
- `PUT /api/admin/modules/{slug}/disable` - Disable module

### Themes

- `GET /api/admin/modules/themes` - List all available themes
- `POST /api/admin/modules/{slug}/install-theme` - Install theme
- `PUT /api/admin/modules/{slug}/activate-theme` - Activate theme (disables others)

## Usage Examples

### List Modules

```bash
curl -X GET http://localhost/api/admin/modules \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Upload Module

```bash
curl -X POST http://localhost/api/admin/modules/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@module.zip" \
  -F "type=module"
```

### Install Module

```bash
curl -X POST http://localhost/api/admin/modules/example-module/install \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Enable/Disable Module

```bash
# Enable
curl -X PUT http://localhost/api/admin/modules/example-module/enable \
  -H "Authorization: Bearer YOUR_TOKEN"

# Disable
curl -X PUT http://localhost/api/admin/modules/example-module/disable \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Uninstall Module

```bash
curl -X DELETE http://localhost/api/admin/modules/example-module \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Creating a New Module

### Method 1: Using the API

```bash
curl -X POST http://localhost/api/admin/modules/create \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "slug": "my-module",
    "name": "My Module"
  }'
```

This creates the directory structure with a basic module.json file.

### Method 2: Manual Creation

1. Create directory: `modules/my-module/`
2. Create `module.json` with required fields
3. Add your code in `src/`, routes in `routes/`, etc.
4. Upload or install via API

## Installation Process

When a module is installed:

1. **Validation**: Checks module.json exists and is valid
2. **Dependency Check**: Verifies all required modules are installed and enabled
3. **Database Transaction**: Begins transaction for rollback safety
4. **Migrations**: Runs module migrations from `database/migrations/`
5. **Assets**: Copies `assets/` to `public/modules/{slug}/`
6. **Database Record**: Creates entry in `installed_modules` table
7. **Custom Installer**: Runs `Installer::install()` if exists
8. **Cache Clear**: Clears config, route, and view caches

## Uninstallation Process

When a module is uninstalled:

1. **Custom Uninstaller**: Runs `Installer::uninstall()` if exists
2. **Rollback Migrations**: Rolls back module migrations
3. **Remove Assets**: Deletes `public/modules/{slug}/`
4. **Database Cleanup**: Removes record from `installed_modules`
5. **Cache Clear**: Clears config, route, and view caches

## Theme System

Themes work similarly to modules but:

- Only one theme can be active at a time
- Activating a theme disables all other themes
- Themes typically don't have migrations
- Themes focus on views and assets

## Best Practices

1. **Versioning**: Use semantic versioning (MAJOR.MINOR.PATCH)
2. **Dependencies**: Always declare module dependencies
3. **Migrations**: Use timestamped migrations to avoid conflicts
4. **Namespaces**: Use unique PSR-4 namespaces for your modules
5. **Assets**: Keep assets in `assets/` directory (auto-copied to public)
6. **Testing**: Test installation, activation, and uninstallation thoroughly
7. **Documentation**: Include README.md with usage instructions

## Module Examples

### Simple Module (No Database)

```
simple-module/
├── module.json
├── README.md
├── routes/
│   └── web.php
└── assets/
    └── js/
        └── script.js
```

### Complex Module (With Database)

```
complex-module/
├── module.json
├── README.md
├── src/
│   ├── Installer.php
│   ├── Controllers/
│   └── Services/
├── routes/
│   ├── web.php
│   └── api.php
├── database/
│   ├── migrations/
│   └── seeders/
├── views/
└── assets/
```

## Troubleshooting

### Module Won't Install

- Check `module.json` is valid JSON
- Verify all required fields are present
- Check dependencies are met
- Review Laravel logs in `storage/logs/`

### Module Installed But Not Working

- Verify module is enabled: `GET /api/admin/modules`
- Clear caches: `php artisan config:clear && php artisan route:clear`
- Check module routes are registered
- Verify middleware is correct

### Migration Errors

- Ensure migrations have unique timestamps
- Check database connection
- Review migration rollback if uninstall fails

## Security

- Module installation is restricted to admin users only
- ZIP extraction validates directory structure
- SQL migrations run in transactions for rollback
- Module code should follow Laravel security best practices

## Future Enhancements

- [ ] Module marketplace/repository
- [ ] Auto-updates for modules
- [ ] Module permissions system
- [ ] Module hooks/events system
- [ ] Web-based module editor
- [ ] Module dependency graph visualization
