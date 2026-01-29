#!/bin/bash

##############################################################################
# Archive Legacy Code - Move old files offline for reference
# Preserves everything for transition but cleans working directory
##############################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║     Archive Legacy Code - Backup for Transition           ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Check we're in the right place
if [ ! -f "index.php" ] || [ ! -d "modules" ]; then
    echo -e "${RED}Error: Legacy code not found in current directory${NC}"
    echo "Please run from /var/www/html"
    exit 1
fi

# Create archive directory with timestamp
ARCHIVE_DIR="/var/www/legacy-backup-$(date +%Y%m%d_%H%M%S)"
echo -e "${YELLOW}Creating archive directory: $ARCHIVE_DIR${NC}"
mkdir -p "$ARCHIVE_DIR"

echo ""
echo -e "${YELLOW}Step 1/5: Backing up legacy PHP code...${NC}"

# Move legacy code (keep for reference)
echo "Moving modules..."
mv modules "$ARCHIVE_DIR/" 2>/dev/null || true

echo "Moving class files..."
mv class "$ARCHIVE_DIR/" 2>/dev/null || true

echo "Moving themes..."
mv themes "$ARCHIVE_DIR/" 2>/dev/null || true

echo "Moving cron..."
mv cron "$ARCHIVE_DIR/" 2>/dev/null || true

echo "Moving vendor (legacy Composer)..."
mv vendor "$ARCHIVE_DIR/" 2>/dev/null || true

echo "Moving src (custom framework)..."
mv src "$ARCHIVE_DIR/" 2>/dev/null || true

echo -e "${GREEN}✓ Legacy code archived${NC}"

echo ""
echo -e "${YELLOW}Step 2/5: Backing up configuration files...${NC}"

# Copy (don't move) important config files
cp config.php "$ARCHIVE_DIR/" 2>/dev/null || true
cp dbconn.php "$ARCHIVE_DIR/" 2>/dev/null || true
cp init.php "$ARCHIVE_DIR/" 2>/dev/null || true

# Move legacy PHP files but keep a reference list
find . -maxdepth 1 -name "*.php" -type f ! -name "install-*.php" -exec mv {} "$ARCHIVE_DIR/" \; 2>/dev/null || true

echo -e "${GREEN}✓ Configuration backed up${NC}"

echo ""
echo -e "${YELLOW}Step 3/5: Backing up old WebSocket server...${NC}"

mv websocket-server.php "$ARCHIVE_DIR/" 2>/dev/null || true
mv fix-websocket-autoload.sh "$ARCHIVE_DIR/" 2>/dev/null || true

echo -e "${GREEN}✓ WebSocket files archived${NC}"

echo ""
echo -e "${YELLOW}Step 4/5: Preserving database dumps and documentation...${NC}"

# Keep SQL files but copy to archive
cp *.sql "$ARCHIVE_DIR/" 2>/dev/null || true
cp *.md "$ARCHIVE_DIR/" 2>/dev/null || true

# Keep old docker files in archive
cp docker-compose.yml "$ARCHIVE_DIR/docker-compose-legacy.yml" 2>/dev/null || true

echo -e "${GREEN}✓ Documentation preserved${NC}"

echo ""
echo -e "${YELLOW}Step 5/5: Creating reference index...${NC}"

# Create a README in archive
cat > "$ARCHIVE_DIR/README_ARCHIVE.md" << 'EOF'
# Legacy Code Archive

This directory contains the original Gangster Legends game code archived during Laravel migration.

## Contents

- **modules/** - All game modules (chat, gang, economy, etc.)
- **class/** - Core PHP classes
- **themes/** - Frontend themes (admin, default, newadmin)
- **cron/** - Cron jobs and scheduled tasks
- **vendor/** - Old Composer dependencies
- **src/** - Custom framework core
- ***.php** - Legacy PHP files (index.php, config.php, etc.)
- **websocket-server.php** - Old Ratchet WebSocket server

## Purpose

These files are kept for reference during migration:
- Database schema reference
- Business logic extraction
- Feature implementation reference
- Configuration values
- Custom code patterns

## Migration Checklist

As you migrate features to Laravel, mark them here:

- [ ] Authentication system
- [ ] Chat module
- [ ] Gang system
- [ ] Economy/banking
- [ ] Crime mechanics
- [ ] Items/inventory
- [ ] Admin panel
- [ ] Moderation tools
- [ ] API endpoints
- [ ] WebSocket real-time features

## Database

Database tables are NOT affected by this archive. They remain in the database for Laravel to use.

## Important Notes

- Database credentials are in config.php
- WebSocket port was 8080/8081
- Session handling was file-based
- Custom autoloader in init.php

## Restoring (Emergency)

If you need to restore legacy code:

```bash
# Stop Laravel
docker-compose -f docker-compose-laravel.yml down

# Copy files back
cp -r /path/to/this/archive/* /var/www/html/

# Restart legacy Docker
docker-compose up -d
```

---

Archived: $(date)
EOF

echo -e "${GREEN}✓ Archive index created${NC}"

# Create a quick reference symlink
ln -sf "$ARCHIVE_DIR" /var/www/legacy-code 2>/dev/null || true

echo ""
echo -e "${GREEN}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║            Legacy Code Archived Successfully! ✓           ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}Archive Location:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "  ${GREEN}•${NC} Full path:  ${BLUE}$ARCHIVE_DIR${NC}"
echo -e "  ${GREEN}•${NC} Symlink:    ${BLUE}/var/www/legacy-code${NC}"
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}What's Left in /var/www/html:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
ls -la /var/www/html/
echo ""

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}Next Steps:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${GREEN}1. Install Laravel:${NC}"
echo "   bash install-laravel-docker.sh"
echo ""
echo -e "${GREEN}2. Reference legacy code when needed:${NC}"
echo "   ls /var/www/legacy-code/"
echo "   cat /var/www/legacy-code/modules/installed/chat/chat.inc.php"
echo ""
echo -e "${GREEN}3. Extract database credentials:${NC}"
echo "   cat /var/www/legacy-code/config.php | grep -E 'DB_|database'"
echo ""
echo -e "${YELLOW}Database is NOT affected - all tables remain intact!${NC}"
echo ""
echo -e "${GREEN}Ready for fresh Laravel installation! 🚀${NC}"
echo ""
