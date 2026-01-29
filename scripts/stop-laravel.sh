#!/bin/bash

##############################################################################
# Stop Laravel Services
# Stops all Laravel development servers
##############################################################################

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}Stopping Laravel services...${NC}"
echo ""

# Check for PID file
if [ -f "/tmp/laravel-services.pids" ]; then
    PIDS=$(cat /tmp/laravel-services.pids)
    echo -e "${YELLOW}Killing processes: $PIDS${NC}"
    kill $PIDS 2>/dev/null || true
    rm /tmp/laravel-services.pids
fi

# Kill any remaining Laravel processes
echo -e "${YELLOW}Stopping all Laravel processes...${NC}"
pkill -f "php artisan serve" || true
pkill -f "php artisan websockets:serve" || true
pkill -f "npm run dev" || true
pkill -f "node.*vite" || true

sleep 2

# Check if anything is still running
if pgrep -f "php artisan" > /dev/null; then
    echo -e "${RED}Some processes are still running, forcing kill...${NC}"
    pkill -9 -f "php artisan" || true
fi

echo ""
echo -e "${GREEN}✓ All Laravel services stopped${NC}"
echo ""
