#!/bin/bash

##############################################################################
# Database Configuration Helper
# Extracts database credentials from existing config.php and configures Laravel
##############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}Database Configuration Helper${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Check if we're in the right directory
if [ ! -f "../config.php" ] && [ ! -f "config.php" ]; then
    echo -e "${RED}Error: config.php not found${NC}"
    echo "Please run this from /var/www/html or /var/www/html/laravel-api"
    exit 1
fi

CONFIG_FILE="config.php"
if [ ! -f "$CONFIG_FILE" ]; then
    CONFIG_FILE="../config.php"
fi

echo -e "${YELLOW}Extracting database credentials from $CONFIG_FILE...${NC}"
echo ""

# Try to extract database info (this is a simple approach)
# You may need to adjust based on your actual config.php structure

echo -e "${GREEN}Please provide your database credentials:${NC}"
echo ""

read -p "Database Host [127.0.0.1]: " DB_HOST
DB_HOST=${DB_HOST:-127.0.0.1}

read -p "Database Port [3306]: " DB_PORT
DB_PORT=${DB_PORT:-3306}

read -p "Database Name: " DB_NAME
if [ -z "$DB_NAME" ]; then
    echo -e "${RED}Database name is required${NC}"
    exit 1
fi

read -p "Database Username: " DB_USER
if [ -z "$DB_USER" ]; then
    echo -e "${RED}Database username is required${NC}"
    exit 1
fi

read -sp "Database Password: " DB_PASS
echo ""
echo ""

# Navigate to laravel-api directory
if [ ! -d "laravel-api" ]; then
    echo -e "${RED}Error: laravel-api directory not found${NC}"
    echo "Please run install-laravel.sh first"
    exit 1
fi

cd laravel-api

# Backup existing .env if it exists
if [ -f ".env" ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    echo -e "${YELLOW}Backed up existing .env${NC}"
fi

echo -e "${YELLOW}Updating .env file...${NC}"

# Update database credentials in .env
sed -i "s/^DB_HOST=.*/DB_HOST=$DB_HOST/" .env
sed -i "s/^DB_PORT=.*/DB_PORT=$DB_PORT/" .env
sed -i "s/^DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

echo -e "${GREEN}✓ Database configuration updated${NC}"
echo ""

# Test database connection
echo -e "${YELLOW}Testing database connection...${NC}"

php artisan config:clear > /dev/null 2>&1

if php artisan db:show > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Database connection successful!${NC}"
    echo ""
    
    # Show database info
    echo -e "${BLUE}Database Information:${NC}"
    php artisan db:show
    echo ""
    
    # Ask if user wants to run migrations
    echo -e "${YELLOW}Ready to run migrations?${NC}"
    echo "This will create Laravel tables (users, sessions, jobs, etc.)"
    echo "It will NOT modify your existing tables."
    echo ""
    read -p "Run migrations now? (y/n) " -n 1 -r
    echo ""
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}Running migrations...${NC}"
        php artisan migrate --force
        echo -e "${GREEN}✓ Migrations complete${NC}"
        echo ""
        
        # Ask about creating admin user
        echo -e "${YELLOW}Create Filament admin user?${NC}"
        read -p "Create admin user now? (y/n) " -n 1 -r
        echo ""
        
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            php artisan make:filament-user
        fi
    fi
    
else
    echo -e "${RED}✗ Database connection failed${NC}"
    echo ""
    echo "Please check your credentials and try again:"
    echo "  cd /var/www/html/laravel-api"
    echo "  nano .env"
    echo "  php artisan config:clear"
    echo "  php artisan db:show"
    exit 1
fi

echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  Database Configuration Complete! ✓   ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}Next Steps:${NC}"
echo "  1. Test Laravel: php artisan serve --host=0.0.0.0 --port=8000"
echo "  2. Start WebSockets: php artisan websockets:serve"
echo "  3. Access admin: http://localhost:8000/admin"
echo ""
