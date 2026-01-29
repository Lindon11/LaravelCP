#!/bin/bash

##############################################################################
# Docker Quick Start - Start all Laravel services
##############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}"
echo "╔════════════════════════════════════════════╗"
echo "║    Criminal Empire - Starting Docker      ║"
echo "╚════════════════════════════════════════════╝"
echo -e "${NC}"

# Check if docker-compose file exists
if [ ! -f "docker-compose-laravel.yml" ]; then
    echo -e "${RED}Error: docker-compose-laravel.yml not found${NC}"
    echo "Please run install-laravel-docker.sh first"
    exit 1
fi

# Check if Laravel directory exists
if [ ! -d "laravel-api" ]; then
    echo -e "${RED}Error: laravel-api directory not found${NC}"
    echo "Please run install-laravel-docker.sh first"
    exit 1
fi

echo -e "${YELLOW}Starting Docker containers...${NC}"
docker-compose -f docker-compose-laravel.yml up -d

echo ""
echo -e "${YELLOW}Waiting for services to be ready...${NC}"
sleep 5

# Check if containers are running
if docker-compose -f docker-compose-laravel.yml ps | grep -q "Up"; then
    echo -e "${GREEN}✓ Containers are running${NC}"
else
    echo -e "${RED}✗ Some containers failed to start${NC}"
    docker-compose -f docker-compose-laravel.yml ps
    exit 1
fi

echo ""
echo -e "${GREEN}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║            All Services Running! 🐳                       ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}Access Points:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "  ${GREEN}•${NC} Laravel App:     ${BLUE}http://web.gangster-legends.orb.local:8000${NC}"
echo -e "  ${GREEN}•${NC} Admin Panel:     ${BLUE}http://web.gangster-legends.orb.local:8000/admin${NC}"
echo -e "  ${GREEN}•${NC} WebSocket Debug: ${BLUE}http://web.gangster-legends.orb.local:8000/laravel-websockets${NC}"
echo -e "  ${GREEN}•${NC} phpMyAdmin:      ${BLUE}http://web.gangster-legends.orb.local:8081${NC}"
echo -e "  ${GREEN}•${NC} Mailpit:         ${BLUE}http://web.gangster-legends.orb.local:8025${NC}"
echo -e "  ${GREEN}•${NC} WebSocket Port:  ${BLUE}ws://web.gangster-legends.orb.local:6001${NC}"
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}Running Containers:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
docker-compose -f docker-compose-laravel.yml ps
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}Useful Commands:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "  ${GREEN}View logs:${NC}"
echo "    docker-compose -f docker-compose-laravel.yml logs -f"
echo ""
echo -e "  ${GREEN}Stop services:${NC}"
echo "    docker-compose -f docker-compose-laravel.yml down"
echo ""
echo -e "  ${GREEN}Restart a service:${NC}"
echo "    docker-compose -f docker-compose-laravel.yml restart [service]"
echo ""
echo -e "  ${GREEN}Execute artisan command:${NC}"
echo "    docker-compose -f docker-compose-laravel.yml exec laravel php artisan [command]"
echo ""
echo -e "  ${GREEN}Access Laravel shell:${NC}"
echo "    docker-compose -f docker-compose-laravel.yml exec laravel bash"
echo ""
echo -e "  ${GREEN}View database:${NC}"
echo "    docker-compose -f docker-compose-laravel.yml exec db mysql -u dev -pdev gangster_legends"
echo ""
echo -e "${GREEN}Press Ctrl+C to exit (services will keep running)${NC}"
echo ""
