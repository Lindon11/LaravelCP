#!/bin/bash

##############################################################################
# Quick Start - Run all services for development
# Starts Laravel dev server, WebSocket server, and frontend watcher
##############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

cd /var/www/html/laravel-api

echo -e "${BLUE}"
echo "╔════════════════════════════════════════════╗"
echo "║    Criminal Empire - Laravel Services     ║"
echo "╚════════════════════════════════════════════╝"
echo -e "${NC}"

# Check if .env exists
if [ ! -f ".env" ]; then
    echo -e "${RED}Error: .env file not found${NC}"
    echo "Please run install-laravel.sh first"
    exit 1
fi

# Clear caches
echo -e "${YELLOW}Clearing caches...${NC}"
php artisan config:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1
php artisan route:clear > /dev/null 2>&1
php artisan view:clear > /dev/null 2>&1

echo -e "${GREEN}✓ Caches cleared${NC}"
echo ""

# Check database connection
echo -e "${YELLOW}Checking database connection...${NC}"
if php artisan db:show > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Database connected${NC}"
else
    echo -e "${RED}✗ Database connection failed${NC}"
    echo "Please configure database: bash configure-database.sh"
    exit 1
fi

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}Starting services...${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Kill existing processes
echo -e "${YELLOW}Stopping any existing services...${NC}"
pkill -f "php artisan serve" || true
pkill -f "php artisan websockets:serve" || true
pkill -f "npm run dev" || true
sleep 2

# Start WebSocket server
echo -e "${GREEN}1. Starting Laravel WebSocket server...${NC}"
nohup php artisan websockets:serve > /tmp/laravel-websockets.log 2>&1 &
WS_PID=$!
echo -e "   ${BLUE}→ PID: $WS_PID${NC}"
echo -e "   ${BLUE}→ Port: 6001${NC}"
echo -e "   ${BLUE}→ Debug: http://localhost:8000/laravel-websockets${NC}"
sleep 2

# Start Laravel dev server
echo -e "${GREEN}2. Starting Laravel development server...${NC}"
nohup php artisan serve --host=0.0.0.0 --port=8000 > /tmp/laravel-server.log 2>&1 &
SERVER_PID=$!
echo -e "   ${BLUE}→ PID: $SERVER_PID${NC}"
echo -e "   ${BLUE}→ URL: http://localhost:8000${NC}"
echo -e "   ${BLUE}→ Admin: http://localhost:8000/admin${NC}"
sleep 2

# Start Vite dev server (for hot module reloading)
echo -e "${GREEN}3. Starting Vite frontend development server...${NC}"
nohup npm run dev > /tmp/laravel-vite.log 2>&1 &
VITE_PID=$!
echo -e "   ${BLUE}→ PID: $VITE_PID${NC}"
echo -e "   ${BLUE}→ Hot reload enabled${NC}"

sleep 3

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║            All Services Running! 🚀                    ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}Access Points:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "  ${GREEN}•${NC} Laravel App:     ${BLUE}http://localhost:8000${NC}"
echo -e "  ${GREEN}•${NC} Admin Panel:     ${BLUE}http://localhost:8000/admin${NC}"
echo -e "  ${GREEN}•${NC} WebSocket Debug: ${BLUE}http://localhost:8000/laravel-websockets${NC}"
echo -e "  ${GREEN}•${NC} API:             ${BLUE}http://localhost:8000/api${NC}"
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}Process IDs:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "  WebSocket Server: ${GREEN}$WS_PID${NC}"
echo -e "  Laravel Server:   ${GREEN}$SERVER_PID${NC}"
echo -e "  Vite Dev Server:  ${GREEN}$VITE_PID${NC}"
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}Logs:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "  ${GREEN}•${NC} tail -f /tmp/laravel-websockets.log"
echo -e "  ${GREEN}•${NC} tail -f /tmp/laravel-server.log"
echo -e "  ${GREEN}•${NC} tail -f /tmp/laravel-vite.log"
echo -e "  ${GREEN}•${NC} tail -f storage/logs/laravel.log"
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}Stop All Services:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "  bash /var/www/html/stop-laravel.sh"
echo ""
echo -e "  Or manually:"
echo -e "    kill $WS_PID $SERVER_PID $VITE_PID"
echo ""

# Save PIDs to file for stop script
echo "$WS_PID $SERVER_PID $VITE_PID" > /tmp/laravel-services.pids

echo -e "${GREEN}Press Ctrl+C to view live logs, or just leave running...${NC}"
echo ""

# Follow logs (optional - comment out if you want script to exit)
# trap 'echo ""; echo "Services still running in background. Use stop-laravel.sh to stop."; exit 0' INT
# tail -f /tmp/laravel-server.log
