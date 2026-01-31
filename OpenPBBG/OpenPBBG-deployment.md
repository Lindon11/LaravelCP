# OpenPBBG Deployment Guide

## Table of Contents
- [Requirements](#requirements)
- [Local Development Setup](#local-development-setup)
- [Production Deployment](#production-deployment)
- [Environment Configuration](#environment-configuration)
- [Connecting to LaravelCP](#connecting-to-laravelcp)
- [Troubleshooting](#troubleshooting)

---

## Requirements

### Development Environment
- Docker & Docker Compose (recommended)
- OR Node.js 20+, NPM

### Production Server
- Ubuntu 20.04+ / Debian 11+ (recommended)
- Node.js 20+ & NPM
- Nginx or Apache
- SSL Certificate (Let's Encrypt recommended)
- Minimum 512MB RAM, 1 CPU core

---

## Local Development Setup

### Using Docker (Recommended)

1. **Clone the repository**
   ```bash
   cd /path/to/your/projects
   git clone https://github.com/YourOrg/OpenPBBG.git
   cd OpenPBBG
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Update `.env` for development**
   ```env
   VITE_API_URL=http://localhost:8001
   VITE_APP_NAME=OpenPBBG
   VITE_WS_URL=ws://localhost:6001
   ```

4. **Install dependencies**
   ```bash
   npm install
   ```

5. **Start development server**
   ```bash
   npm run dev
   ```

6. **Access the application**
   - Frontend: http://localhost:5175
   - API Backend: http://localhost:8001/api (LaravelCP must be running)

### Without Docker

```bash
# Install dependencies
npm install

# Start dev server
npm run dev -- --host 0.0.0.0 --port 5175
```

---

## Production Deployment

### 1. Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install -y nginx

# Install PM2 (Node.js process manager)
sudo npm install -g pm2
```

### 2. Application Deployment

```bash
# Create web directory
sudo mkdir -p /var/www/openpbbg
sudo chown -R $USER:www-data /var/www/openpbbg

# Clone repository
cd /var/www/openpbbg
git clone https://github.com/YourOrg/OpenPBBG.git .

# Set permissions
sudo chown -R www-data:www-data /var/www/openpbbg
sudo chmod -R 755 /var/www/openpbbg
```

### 3. Environment Configuration

```bash
# Copy and edit environment file
cp .env.example .env
nano .env
```

**Production `.env` settings:**
```env
VITE_API_URL=https://api.yourdomain.com
VITE_APP_NAME=OpenPBBG
VITE_WS_URL=wss://api.yourdomain.com:6001
```

### 4. Build Application

```bash
cd /var/www/openpbbg

# Install dependencies
npm install

# Build for production
npm run build

# The built files will be in the 'dist' directory
```

### 5. Nginx Configuration

Create `/etc/nginx/sites-available/openpbbg`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name app.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name app.yourdomain.com;

    root /var/www/openpbbg/dist;
    index index.html;

    # SSL Configuration (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/app.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/app.yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Logs
    access_log /var/log/nginx/openpbbg-access.log;
    error_log /var/log/nginx/openpbbg-error.log;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/json application/xml+rss image/svg+xml;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location ~* \.(css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Enable site:**
```bash
sudo ln -s /etc/nginx/sites-available/openpbbg /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 6. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d app.yourdomain.com

# Auto-renewal is configured automatically
```

### 7. File Permissions (Final Check)

```bash
cd /var/www/openpbbg
sudo chown -R www-data:www-data .
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;
```

---

## Environment Configuration

### Key Environment Variables

| Variable | Development | Production | Description |
|----------|-------------|------------|-------------|
| `VITE_API_URL` | http://localhost:8001 | https://api.yourdomain.com | Backend API URL |
| `VITE_APP_NAME` | OpenPBBG | OpenPBBG | Application name |
| `VITE_WS_URL` | ws://localhost:6001 | wss://api.yourdomain.com:6001 | WebSocket URL (optional) |

### Build-time vs Runtime Variables

**Important:** Vite environment variables are embedded at **build time**, not runtime.

This means:
- Changes to `.env` after building require rebuilding: `npm run build`
- The `dist/` folder contains hardcoded API URLs
- You cannot change the API URL without rebuilding

---

## Connecting to LaravelCP

OpenPBBG is a **frontend-only** application that connects to LaravelCP backend.

### Setup Process

1. **Ensure LaravelCP is deployed first** (see [LaravelCP DEPLOYMENT.md](../LaravelCP/DEPLOYMENT.md))

2. **Configure CORS in LaravelCP**
   
   Update LaravelCP `.env`:
   ```env
   SANCTUM_STATEFUL_DOMAINS=api.yourdomain.com,app.yourdomain.com
   SESSION_DOMAIN=.yourdomain.com
   ```

3. **Build OpenPBBG with correct API URL**
   
   Update OpenPBBG `.env`:
   ```env
   VITE_API_URL=https://api.yourdomain.com
   ```
   
   Then build:
   ```bash
   npm run build
   ```

4. **Test connection**
   - Visit https://app.yourdomain.com
   - Open browser console (F12)
   - Check for API calls to https://api.yourdomain.com
   - Should see successful authentication requests

### Authentication Flow

1. User visits `app.yourdomain.com` (OpenPBBG)
2. OpenPBBG makes API call to `api.yourdomain.com/api/auth/login` (LaravelCP)
3. LaravelCP returns Sanctum token
4. OpenPBBG stores token and includes it in subsequent API requests
5. All game data fetched from LaravelCP API endpoints

### API Endpoints Used by OpenPBBG

- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout
- `GET /api/user` - Get current user data
- `GET /api/crimes` - Get available crimes
- `POST /api/crimes/{id}/commit` - Commit a crime
- `GET /api/locations` - Get locations
- `GET /api/drugs` - Get drugs for trading
- `POST /api/drugs/buy` - Buy drugs
- `POST /api/drugs/sell` - Sell drugs
- And more...

---

## Updating Production Application

```bash
cd /var/www/openpbbg

# Pull latest changes
git pull origin main

# Install dependencies (if package.json changed)
npm install

# Rebuild application
npm run build

# Reload Nginx (if config changed)
sudo nginx -t
sudo systemctl reload nginx
```

---

## Alternative: Using Node.js Server (SSR/Preview)

If you want to serve with Vite's preview server instead of static files:

### 1. Build the application
```bash
npm run build
```

### 2. Setup PM2 to serve with Vite preview
```bash
pm2 start npm --name "openpbbg" -- run preview -- --port 5175 --host 0.0.0.0
pm2 save
pm2 startup
```

### 3. Configure Nginx as reverse proxy
```nginx
server {
    listen 80;
    server_name app.yourdomain.com;

    location / {
        proxy_pass http://localhost:5175;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

---

## Troubleshooting

### Blank Page / White Screen
```bash
# Check if build completed successfully
ls -la dist/

# Check for index.html
cat dist/index.html

# Rebuild
npm run build

# Check browser console for errors (F12)
```

### API Connection Failed
```bash
# Verify API URL in built files
grep -r "VITE_API_URL" dist/assets/*.js

# Should show: https://api.yourdomain.com

# If wrong, update .env and rebuild
nano .env
npm run build
```

### CORS Errors
- Ensure LaravelCP `SANCTUM_STATEFUL_DOMAINS` includes OpenPBBG domain
- Check `SESSION_DOMAIN` in LaravelCP matches parent domain
- Verify both sites use HTTPS (or both HTTP in development)
- Check browser console for specific CORS error messages

### Assets Not Loading (404)
```bash
# Check nginx config base path
# Should be: root /var/www/openpbbg/dist;

# Check file permissions
ls -la /var/www/openpbbg/dist/

# Ensure nginx can read files
sudo chown -R www-data:www-data /var/www/openpbbg/dist/
```

### Build Errors
```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear Vite cache
rm -rf node_modules/.vite

# Try building again
npm run build
```

### Outdated Build
If changes aren't reflecting:
```bash
# Clear dist folder
rm -rf dist/

# Rebuild
npm run build

# Force browser to reload (Ctrl+Shift+R or Cmd+Shift+R)
```

---

## Development Tips

### Running with Hot Module Replacement (HMR)
```bash
npm run dev
# Access at http://localhost:5175
# Changes auto-reload in browser
```

### Building for Different Environments
```bash
# Development build (larger, with source maps)
npm run build -- --mode development

# Production build (optimized, minified)
npm run build
```

### Previewing Production Build Locally
```bash
npm run build
npm run preview
# Access at http://localhost:4173
```

---

## Performance Optimization

### 1. Enable Brotli Compression (Nginx)
```bash
# Install brotli module
sudo apt install -y nginx-module-brotli
```

Add to nginx config:
```nginx
load_module modules/ngx_http_brotli_filter_module.so;
load_module modules/ngx_http_brotli_static_module.so;

http {
    brotli on;
    brotli_types text/plain text/css text/xml text/javascript application/javascript application/json;
}
```

### 2. Optimize Build
Update `vite.config.js`:
```javascript
export default defineConfig({
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['vue', 'vue-router', 'pinia'],
          utils: ['axios']
        }
      }
    },
    chunkSizeWarningLimit: 1000,
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true
      }
    }
  }
})
```

### 3. Enable HTTP/2 Push (Nginx)
```nginx
location = /index.html {
    http2_push /assets/index.css;
    http2_push /assets/index.js;
}
```

---

## Deployment Checklist

- [ ] LaravelCP backend is deployed and accessible
- [ ] `.env` configured with production API URL
- [ ] Application built with `npm run build`
- [ ] Nginx configured and tested
- [ ] SSL certificate installed
- [ ] CORS configured in LaravelCP
- [ ] Test login/registration flow
- [ ] Test all major features (crimes, drugs, etc.)
- [ ] Check browser console for errors
- [ ] Verify API calls are HTTPS
- [ ] Check mobile responsiveness
- [ ] Monitor access/error logs

---

## CI/CD Pipeline Example

### GitHub Actions (`.github/workflows/deploy.yml`)

```yaml
name: Deploy OpenPBBG

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '20'
          
      - name: Install dependencies
        run: npm ci
        
      - name: Build
        run: npm run build
        env:
          VITE_API_URL: ${{ secrets.VITE_API_URL }}
          
      - name: Deploy to server
        uses: easingthemes/ssh-deploy@v2
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
          REMOTE_HOST: ${{ secrets.REMOTE_HOST }}
          REMOTE_USER: ${{ secrets.REMOTE_USER }}
          TARGET: /var/www/openpbbg/dist/
          SOURCE: dist/
```

---

## Monitoring

### Access Logs
```bash
tail -f /var/log/nginx/openpbbg-access.log
```

### Error Logs
```bash
tail -f /var/log/nginx/openpbbg-error.log
```

### Real-time Log Monitoring
```bash
sudo apt install -y goaccess

# Generate real-time report
goaccess /var/log/nginx/openpbbg-access.log -o /var/www/html/report.html --log-format=COMBINED --real-time-html
```

---

## Support

For issues and questions:
- GitHub Issues: https://github.com/YourOrg/OpenPBBG/issues
- Vue 3 Docs: https://vuejs.org/guide/
- Vite Docs: https://vitejs.dev/guide/

---

**Last Updated:** January 2026
