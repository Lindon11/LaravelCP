#!/bin/bash

##############################################################################
# Stop Docker Laravel Services
##############################################################################

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}Stopping Docker Laravel services...${NC}"

if [ ! -f "docker-compose-laravel.yml" ]; then
    echo -e "${RED}Error: docker-compose-laravel.yml not found${NC}"
    exit 1
fi

docker-compose -f docker-compose-laravel.yml down

echo ""
echo -e "${GREEN}✓ All Laravel services stopped${NC}"
echo ""
echo -e "${YELLOW}To remove volumes (database data):${NC}"
echo "  docker-compose -f docker-compose-laravel.yml down -v"
echo ""
