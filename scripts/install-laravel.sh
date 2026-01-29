#!/bin/bash

##############################################################################
# Laravel 11 Gaming Platform Installer
# Installs complete Laravel foundation with all features for Criminal Empire
##############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║     Laravel 11 Gaming Platform Installation Script        ║"
echo "║                   Criminal Empire                          ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Check if running from /var/www/html
if [ "$PWD" != "/var/www/html" ]; then
    echo -e "${RED}Error: Please run this script from /var/www/html${NC}"
    echo "cd /var/www/html && bash install-laravel.sh"
    exit 1
fi

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo -e "${RED}Error: Composer is not installed${NC}"
    echo "Install it from: https://getcomposer.org"
    exit 1
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo -e "${RED}Error: npm is not installed${NC}"
    echo "Install Node.js from: https://nodejs.org"
    exit 1
fi

echo -e "${YELLOW}Step 1/10: Creating Laravel project...${NC}"
if [ -d "laravel-api" ]; then
    echo -e "${YELLOW}Warning: laravel-api directory already exists${NC}"
    read -p "Remove and reinstall? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        rm -rf laravel-api
    else
        echo -e "${RED}Installation cancelled${NC}"
        exit 1
    fi
fi

composer create-project laravel/laravel laravel-api "11.*" --no-interaction
cd laravel-api

echo -e "${GREEN}✓ Laravel 11 installed${NC}"

echo -e "${YELLOW}Step 2/10: Installing core packages...${NC}"
composer require laravel/jetstream --no-interaction
composer require laravel/reverb --no-interaction
composer require filament/filament:"^3.0" --no-interaction
composer require spatie/laravel-permission --no-interaction
composer require laravel/sanctum --no-interaction

echo -e "${GREEN}✓ Core packages installed${NC}"

echo -e "${YELLOW}Step 3/10: Installing development tools...${NC}"
composer require --dev barryvdh/laravel-debugbar --no-interaction
composer require --dev barryvdh/laravel-ide-helper --no-interaction

echo -e "${GREEN}✓ Development tools installed${NC}"

echo -e "${YELLOW}Step 4/10: Installing Jetstream with Inertia + Vue...${NC}"
php artisan jetstream:install inertia --teams --no-interaction

echo -e "${GREEN}✓ Jetstream installed${NC}"

echo -e "${YELLOW}Step 5/10: Installing frontend dependencies...${NC}"
npm install

echo -e "${GREEN}✓ Frontend dependencies installed${NC}"

echo -e "${YELLOW}Step 6/10: Installing Filament admin panel...${NC}"
php artisan filament:install --panels --no-interaction

echo -e "${GREEN}✓ Filament installed${NC}"

echo -e "${YELLOW}Step 7/10: Publishing configuration files...${NC}"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --no-interaction
php artisan vendor:publish --tag=jetstream-views --no-interaction

echo -e "${GREEN}✓ Configuration files published${NC}"

echo -e "${YELLOW}Step 8/10: Creating environment configuration...${NC}"

# Create .env file from existing config
cat > .env << 'EOF'
APP_NAME="Criminal Empire"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

# IMPORTANT: Update these with your existing database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Laravel Websockets Configuration (replaces Ratchet)
BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=database

# WebSocket Settings (for Laravel Websockets)
LARAVEL_WEBSOCKETS_HOST=0.0.0.0
LARAVEL_WEBSOCKETS_PORT=6001

# Pusher Configuration (for Laravel Websockets compatibility)
PUSHER_APP_ID=local
PUSHER_APP_KEY=local
PUSHER_APP_SECRET=local
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=.criminal-empire.co.uk

# Mail Configuration (copy from existing system)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@criminal-empire.co.uk"
MAIL_FROM_NAME="${APP_NAME}"

# Additional Settings
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
EOF

echo -e "${GREEN}✓ Environment file created${NC}"
echo -e "${YELLOW}⚠ IMPORTANT: Edit .env and update database credentials!${NC}"

echo -e "${YELLOW}Step 9/10: Generating application key...${NC}"
php artisan key:generate --no-interaction

echo -e "${GREEN}✓ Application key generated${NC}"

echo -e "${YELLOW}Step 10/10: Building frontend assets...${NC}"
npm run build

echo -e "${GREEN}✓ Frontend assets built${NC}"

# Generate IDE helper files
echo -e "${YELLOW}Generating IDE helper files...${NC}"
php artisan ide-helper:generate || true
php artisan ide-helper:models --nowrite || true

echo -e "${GREEN}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║            Installation Complete! 🎉                       ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}NEXT STEPS:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${GREEN}1. Configure Database:${NC}"
echo "   cd /var/www/html/laravel-api"
echo "   nano .env  # Update DB_* credentials from your existing config"
echo ""
echo -e "${GREEN}2. Run Migrations:${NC}"
echo "   php artisan migrate"
echo "   (This creates Laravel tables, won't touch your existing tables)"
echo ""
echo -e "${GREEN}3. Create Admin User:${NC}"
echo "   php artisan make:filament-user"
echo "   (Use your existing admin email/password)"
echo ""
echo -e "${GREEN}4. Start Services:${NC}"
echo ""
echo "   # Terminal 1: WebSocket Server"
echo "   php artisan websockets:serve"
echo ""
echo "   # Terminal 2: Laravel Development Server"
echo "   php artisan serve --host=0.0.0.0 --port=8000"
echo ""
echo "   # Terminal 3: Frontend Dev (optional for hot reload)"
echo "   npm run dev"
echo ""
echo -e "${GREEN}5. Access Your New Stack:${NC}"
echo "   • Laravel App:     http://localhost:8000"
echo "   • Admin Panel:     http://localhost:8000/admin"
echo "   • WebSocket Debug: http://localhost:8000/laravel-websockets"
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}PRODUCTION DEPLOYMENT:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "   # Run WebSocket server in background"
echo "   nohup php artisan websockets:serve > /tmp/laravel-ws.log 2>&1 &"
echo ""
echo "   # Add to crontab for scheduler"
echo "   * * * * * cd /var/www/html/laravel-api && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "   # Start queue worker"
echo "   nohup php artisan queue:work --daemon > /tmp/laravel-queue.log 2>&1 &"
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${GREEN}📚 Documentation:${NC}"
echo "   • LARAVEL_STARTER.md - Complete setup guide"
echo "   • Laravel Docs: https://laravel.com/docs/11.x"
echo "   • Filament Docs: https://filamentphp.com/docs"
echo ""
echo -e "${YELLOW}💡 Tip:${NC} Run 'php artisan' to see all available commands"
echo ""
echo -e "${GREEN}Installation directory: /var/www/html/laravel-api${NC}"
echo ""
