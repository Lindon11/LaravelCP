#!/bin/bash

##############################################################################
# Gangster Legends - Easy Installer
# Interactive installation script for non-developers
##############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Unicode symbols
CHECK="✓"
CROSS="✗"
ARROW="→"

clear

echo -e "${BLUE}"
cat << "EOF"
╔════════════════════════════════════════════════════════════╗
║                                                            ║
║        🎮  GANGSTER LEGENDS - GAME ENGINE INSTALLER       ║
║                                                            ║
║            Interactive Setup for Game Managers            ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

echo ""
echo -e "${CYAN}This installer will guide you through setting up your game.${NC}"
echo -e "${CYAN}Press CTRL+C at any time to cancel.${NC}"
echo ""
read -p "Press Enter to continue..."

##############################################################################
# Step 1: Check System Requirements
##############################################################################

echo ""
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Step 1/7: Checking System Requirements                   ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Check PHP
echo -n "Checking PHP version... "
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -r "echo PHP_VERSION;" 2>/dev/null)
    PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;" 2>/dev/null)
    PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;" 2>/dev/null)
    
    if [ "$PHP_MAJOR" -ge 8 ] && [ "$PHP_MINOR" -ge 2 ]; then
        echo -e "${GREEN}${CHECK} PHP $PHP_VERSION${NC}"
    else
        echo -e "${RED}${CROSS} PHP $PHP_VERSION (need 8.2+)${NC}"
        exit 1
    fi
else
    echo -e "${RED}${CROSS} PHP not found${NC}"
    exit 1
fi

# Check Composer
echo -n "Checking Composer... "
if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version --no-ansi 2>/dev/null | grep -oP '\d+\.\d+\.\d+' | head -1)
    echo -e "${GREEN}${CHECK} $COMPOSER_VERSION${NC}"
else
    echo -e "${RED}${CROSS} Composer not found${NC}"
    echo -e "${YELLOW}Install from: https://getcomposer.org/${NC}"
    exit 1
fi

# Check Node.js
echo -n "Checking Node.js... "
if command -v node &> /dev/null; then
    NODE_VERSION=$(node --version 2>/dev/null)
    echo -e "${GREEN}${CHECK} $NODE_VERSION${NC}"
else
    echo -e "${RED}${CROSS} Node.js not found${NC}"
    echo -e "${YELLOW}Install from: https://nodejs.org/${NC}"
    exit 1
fi

# Check npm
echo -n "Checking npm... "
if command -v npm &> /dev/null; then
    NPM_VERSION=$(npm --version 2>/dev/null)
    echo -e "${GREEN}${CHECK} $NPM_VERSION${NC}"
else
    echo -e "${RED}${CROSS} npm not found${NC}"
    exit 1
fi

# Check required PHP extensions
echo ""
echo "Checking PHP extensions:"
REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "mbstring" "openssl" "tokenizer" "json" "curl" "zip" "gd")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    echo -n "  - $ext... "
    if php -m 2>/dev/null | grep -qi "^$ext$"; then
        echo -e "${GREEN}${CHECK}${NC}"
    else
        echo -e "${RED}${CROSS}${NC}"
        MISSING_EXTENSIONS+=($ext)
    fi
done

if [ ${#MISSING_EXTENSIONS[@]} -ne 0 ]; then
    echo ""
    echo -e "${RED}Missing PHP extensions: ${MISSING_EXTENSIONS[*]}${NC}"
    echo -e "${YELLOW}Install them before continuing${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}${CHECK} All system requirements met!${NC}"
read -p "Press Enter to continue..."

##############################################################################
# Step 2: Database Configuration
##############################################################################

echo ""
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Step 2/7: Database Configuration                          ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

echo -e "${CYAN}Enter your database details:${NC}"
echo ""

read -p "Database Host (default: localhost): " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "Database Port (default: 3306): " DB_PORT
DB_PORT=${DB_PORT:-3306}

read -p "Database Name: " DB_DATABASE
while [ -z "$DB_DATABASE" ]; do
    echo -e "${RED}Database name is required${NC}"
    read -p "Database Name: " DB_DATABASE
done

read -p "Database Username: " DB_USERNAME
while [ -z "$DB_USERNAME" ]; do
    echo -e "${RED}Username is required${NC}"
    read -p "Database Username: " DB_USERNAME
done

read -sp "Database Password: " DB_PASSWORD
echo ""

# Test database connection
echo ""
echo -n "Testing database connection... "
if php -r "
    try {
        new PDO('mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null; then
    echo -e "${GREEN}${CHECK} Connected!${NC}"
else
    echo -e "${RED}${CROSS} Connection failed${NC}"
    echo -e "${YELLOW}Please check your credentials and try again${NC}"
    exit 1
fi

##############################################################################
# Step 3: Application Configuration
##############################################################################

echo ""
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Step 3/7: Application Configuration                       ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

read -p "Application Name (default: Gangster Legends): " APP_NAME
APP_NAME=${APP_NAME:-"Gangster Legends"}

read -p "Application URL (e.g., http://localhost): " APP_URL
while [ -z "$APP_URL" ]; do
    echo -e "${RED}Application URL is required${NC}"
    read -p "Application URL: " APP_URL
done

echo ""
echo "Select Environment:"
echo "1) Production (live game)"
echo "2) Development (testing)"
read -p "Choice (1 or 2): " ENV_CHOICE

if [ "$ENV_CHOICE" = "1" ]; then
    APP_ENV="production"
    APP_DEBUG="false"
else
    APP_ENV="local"
    APP_DEBUG="true"
fi

##############################################################################
# Step 4: Create .env File
##############################################################################

echo ""
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Step 4/7: Creating Configuration                          ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

if [ -f .env ]; then
    echo -e "${YELLOW}⚠ .env file already exists${NC}"
    read -p "Overwrite? (y/n): " OVERWRITE
    if [ "$OVERWRITE" != "y" ]; then
        echo "Keeping existing .env file"
    else
        cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
        echo -e "${GREEN}Backed up existing .env${NC}"
    fi
fi

echo -n "Creating .env file... "
cat > .env << EOF
APP_NAME="${APP_NAME}"
APP_ENV=${APP_ENV}
APP_KEY=
APP_DEBUG=${APP_DEBUG}
APP_TIMEZONE=UTC
APP_URL=${APP_URL}

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="\${APP_NAME}"
EOF

echo -e "${GREEN}${CHECK}${NC}"

##############################################################################
# Step 5: Install Dependencies
##############################################################################

echo ""
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Step 5/7: Installing Dependencies                         ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

echo -e "${CYAN}${ARROW} Installing PHP dependencies...${NC}"
composer install --no-interaction --optimize-autoloader

echo ""
echo -e "${CYAN}${ARROW} Installing Node.js dependencies...${NC}"
npm install

echo ""
echo -e "${GREEN}${CHECK} Dependencies installed!${NC}"

##############################################################################
# Step 6: Setup Application
##############################################################################

echo ""
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Step 6/7: Setting Up Application                          ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

echo -e "${CYAN}${ARROW} Generating application key...${NC}"
php artisan key:generate --force

echo ""
echo -e "${CYAN}${ARROW} Running database migrations...${NC}"
php artisan migrate --force

echo ""
echo -e "${CYAN}${ARROW} Creating storage link...${NC}"
php artisan storage:link

echo ""
echo -e "${CYAN}${ARROW} Optimizing application...${NC}"
php artisan optimize

echo ""
echo -e "${CYAN}${ARROW} Building frontend assets...${NC}"
npm run build

echo ""
echo -e "${GREEN}${CHECK} Application setup complete!${NC}"

##############################################################################
# Step 7: Create Admin User
##############################################################################

echo ""
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Step 7/7: Create Admin Account                            ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

echo -e "${CYAN}Create your admin account:${NC}"
echo ""

read -p "Admin Username: " ADMIN_USERNAME
while [ -z "$ADMIN_USERNAME" ]; do
    echo -e "${RED}Username is required${NC}"
    read -p "Admin Username: " ADMIN_USERNAME
done

read -p "Admin Email: " ADMIN_EMAIL
while [ -z "$ADMIN_EMAIL" ]; do
    echo -e "${RED}Email is required${NC}"
    read -p "Admin Email: " ADMIN_EMAIL
done

read -sp "Admin Password: " ADMIN_PASSWORD
echo ""
while [ -z "$ADMIN_PASSWORD" ]; then
    echo -e "${RED}Password is required${NC}"
    read -sp "Admin Password: " ADMIN_PASSWORD
    echo ""
done

read -sp "Confirm Password: " ADMIN_PASSWORD_CONFIRM
echo ""

if [ "$ADMIN_PASSWORD" != "$ADMIN_PASSWORD_CONFIRM" ]; then
    echo -e "${RED}Passwords do not match${NC}"
    exit 1
fi

# Create admin user
php artisan tinker --execute="
\$user = \App\Models\User::create([
    'username' => '$ADMIN_USERNAME',
    'email' => '$ADMIN_EMAIL',
    'password' => bcrypt('$ADMIN_PASSWORD'),
    'email_verified_at' => now(),
]);
echo 'Admin user created: ' . \$user->email . PHP_EOL;
"

##############################################################################
# Installation Complete!
##############################################################################

echo ""
echo -e "${GREEN}"
cat << "EOF"
╔════════════════════════════════════════════════════════════╗
║                                                            ║
║              🎉  INSTALLATION COMPLETE! 🎉                ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

echo ""
echo -e "${GREEN}${CHECK} Your game is ready!${NC}"
echo ""
echo -e "${CYAN}Next Steps:${NC}"
echo ""
echo -e "1. Start the development server:"
echo -e "   ${YELLOW}php artisan serve${NC}"
echo ""
echo -e "2. Access your game:"
echo -e "   ${YELLOW}${APP_URL}${NC}"
echo ""
echo -e "3. Access the admin panel:"
echo -e "   ${YELLOW}${APP_URL}/admin${NC}"
echo -e "   Username: ${YELLOW}${ADMIN_USERNAME}${NC}"
echo ""
echo -e "4. Install modules:"
echo -e "   ${YELLOW}Admin Panel → Admin Panel → Module Manager${NC}"
echo ""
echo -e "${CYAN}Documentation:${NC}"
echo -e "  - Quick Start: ${YELLOW}QUICKSTART.md${NC}"
echo -e "  - Module System: ${YELLOW}MODULE_MANAGER.md${NC}"
echo -e "  - API Docs: ${YELLOW}API_IMPLEMENTATION.md${NC}"
echo ""
echo -e "${GREEN}Happy gaming! 🎮${NC}"
echo ""
