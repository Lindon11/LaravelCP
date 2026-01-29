#!/bin/bash

##############################################################################
# Docker Laravel Installation Script
# Installs Laravel stack using Docker containers - no local dependencies!
##############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║     Laravel 11 + Docker Installation Script               ║"
echo "║              Criminal Empire - Dockerized                  ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Error: Docker is not installed${NC}"
    echo "Install Docker from: https://docs.docker.com/get-docker/"
    exit 1
fi

if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo -e "${RED}Error: Docker Compose is not installed${NC}"
    echo "Install Docker Compose from: https://docs.docker.com/compose/install/"
    exit 1
fi

# Check if we're in /var/www/html
if [ "$PWD" != "/var/www/html" ]; then
    echo -e "${YELLOW}Warning: Not in /var/www/html, continuing anyway...${NC}"
fi

echo -e "${YELLOW}Step 1/8: Creating Laravel project directory...${NC}"

if [ ! -d "laravel-api" ]; then
    echo "Creating laravel-api directory..."
    mkdir -p laravel-api
    echo -e "${GREEN}✓ Directory created${NC}"
else
    echo -e "${YELLOW}laravel-api directory already exists${NC}"
    read -p "Remove and reinstall? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Removing laravel-api..."
        rm -rf laravel-api
        mkdir -p laravel-api
    else
        echo -e "${YELLOW}Keeping existing directory${NC}"
    fi
fi

echo -e "${YELLOW}Step 2/8: Starting database container...${NC}"
docker-compose -f docker-compose-laravel.yml up -d db redis

echo "Waiting for database to be ready..."
sleep 10

echo -e "${GREEN}✓ Database and Redis started${NC}"

echo -e "${YELLOW}Step 3/8: Installing Laravel via Docker...${NC}"

# Create Laravel project using Docker (no local composer needed!)
docker run --rm \
    -v "$(pwd)/laravel-api:/var/www/html" \
    -w /var/www/html \
    composer:latest \
    create-project laravel/laravel . "11.*" --no-interaction

echo -e "${GREEN}✓ Laravel 11 installed${NC}"

echo -e "${YELLOW}Step 4/8: Installing packages via Docker...${NC}"

# Install packages
docker run --rm \
    -v "$(pwd)/laravel-api:/var/www/html" \
    -w /var/www/html \
    composer:latest \
    require laravel/jetstream beyondcode/laravel-websockets filament/filament:"^3.0" spatie/laravel-permission laravel/sanctum --no-interaction

docker run --rm \
    -v "$(pwd)/laravel-api:/var/www/html" \
    -w /var/www/html \
    composer:latest \
    require --dev barryvdh/laravel-debugbar barryvdh/laravel-ide-helper --no-interaction

echo -e "${GREEN}✓ Packages installed${NC}"

echo -e "${YELLOW}Step 5/8: Configuring environment...${NC}"

# Create .env file
cat > laravel-api/.env << 'EOF'
APP_NAME="Criminal Empire"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://web.gangster-legends.orb.local:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

# Database (Docker container)
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=gangster_legends
DB_USERNAME=dev
DB_PASSWORD=dev

# Redis (Docker container)
BROADCAST_DRIVER=pusher
CACHE_DRIVER=redis
QUEUE_CONNECTION=database
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# WebSocket Configuration
LARAVEL_WEBSOCKETS_HOST=0.0.0.0
LARAVEL_WEBSOCKETS_PORT=6001

PUSHER_APP_ID=local
PUSHER_APP_KEY=local
PUSHER_APP_SECRET=local
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1

# Mail (Mailpit container)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@criminal-empire.co.uk"
MAIL_FROM_NAME="${APP_NAME}"

# Vite
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="web.gangster-legends.orb.local"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
EOF

echo -e "${GREEN}✓ Environment configured${NC}"

echo -e "${YELLOW}Step 6/8: Generating application key...${NC}"

docker run --rm \
    -v "$(pwd)/laravel-api:/var/www/html" \
    -w /var/www/html \
    --env-file laravel-api/.env \
    php:8.2-cli \
    php artisan key:generate

echo -e "${GREEN}✓ Application key generated${NC}"

echo -e "${YELLOW}Step 7/8: Installing Jetstream...${NC}"

docker run --rm \
    -v "$(pwd)/laravel-api:/var/www/html" \
    -w /var/www/html \
    php:8.2-cli \
    php artisan jetstream:install inertia --teams

echo -e "${GREEN}✓ Jetstream installed${NC}"

echo -e "${YELLOW}Step 8/8: Building containers...${NC}"

docker-compose -f docker-compose-laravel.yml build

echo -e "${GREEN}✓ Containers built${NC}"

echo -e "${GREEN}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║            Docker Installation Complete! 🐳               ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}NEXT STEPS:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${GREEN}1. Start All Services:${NC}"
echo "   docker-compose -f docker-compose-laravel.yml up -d"
echo ""
echo -e "${GREEN}2. Run Migrations:${NC}"
echo "   docker-compose -f docker-compose-laravel.yml exec laravel php artisan migrate"
echo ""
echo -e "${GREEN}3. Install Frontend Dependencies:${NC}"
echo "   docker-compose -f docker-compose-laravel.yml exec laravel npm install"
echo "   docker-compose -f docker-compose-laravel.yml exec laravel npm run build"
echo ""
echo -e "${GREEN}4. Create Admin User:${NC}"
echo "   docker-compose -f docker-compose-laravel.yml exec laravel php artisan make:filament-user"
echo ""
echo -e "${GREEN}5. Access Your Services:${NC}"
echo "   • Laravel App:     http://web.gangster-legends.orb.local:8000"
echo "   • Admin Panel:     http://web.gangster-legends.orb.local:8000/admin"
echo "   • WebSocket Debug: http://web.gangster-legends.orb.local:8000/laravel-websockets"
echo "   • phpMyAdmin:      http://web.gangster-legends.orb.local:8081"
echo "   • Mailpit:         http://web.gangster-legends.orb.local:8025"
echo "   • Redis:           localhost:6379"
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}USEFUL COMMANDS:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "  # Start all services"
echo "  docker-compose -f docker-compose-laravel.yml up -d"
echo ""
echo "  # Stop all services"
echo "  docker-compose -f docker-compose-laravel.yml down"
echo ""
echo "  # View logs"
echo "  docker-compose -f docker-compose-laravel.yml logs -f"
echo ""
echo "  # Execute artisan commands"
echo "  docker-compose -f docker-compose-laravel.yml exec laravel php artisan [command]"
echo ""
echo "  # Access Laravel container shell"
echo "  docker-compose -f docker-compose-laravel.yml exec laravel bash"
echo ""
echo "  # Rebuild containers"
echo "  docker-compose -f docker-compose-laravel.yml up -d --build"
echo ""
echo -e "${GREEN}No local PHP, Composer, or Node.js required! Everything runs in Docker 🎉${NC}"
echo ""
