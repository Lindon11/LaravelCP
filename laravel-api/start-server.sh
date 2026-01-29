#!/bin/bash
# Laravel Development Server Keeper
# This script ensures the Laravel server stays running

cd /var/www/html/laravel-api

# Kill any existing Laravel server
pkill -f "php artisan serve" || true

# Start server in background with nohup
nohup php artisan serve --host=0.0.0.0 --port=8000 > /var/www/html/laravel-api/storage/logs/server.log 2>&1 &

echo "Laravel server started in background (PID: $!)"
echo "Logs: /var/www/html/laravel-api/storage/logs/server.log"
echo "URL: http://web.gangster-legends.orb.local:8000"
