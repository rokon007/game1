#!/bin/bash

set -e

echo "🚀 Starting Crash Game Application..."

# Wait for database
echo "⏳ Waiting for database..."
max_attempts=30
attempt=0

until php artisan db:show 2>/dev/null || [ $attempt -eq $max_attempts ]; do
    echo "Database not ready, waiting... (attempt $((attempt+1))/$max_attempts)"
    sleep 2
    attempt=$((attempt+1))
done

if [ $attempt -eq $max_attempts ]; then
    echo "⚠️  Database connection timeout, proceeding anyway..."
else
    echo "✅ Database connected"
fi

# Run migrations
echo "🔄 Running migrations..."
php artisan migrate --force || echo "⚠️  Migration failed, continuing..."

# Clear and cache
echo "🧹 Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
php artisan storage:link 2>/dev/null || true

# Set permissions
echo "🔐 Setting permissions..."
chown -R unit:unit /var/www/html/storage
chown -R unit:unit /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Create log directory for crash game
mkdir -p /var/www/html/storage/logs
touch /var/www/html/storage/logs/crash-game.log
touch /var/www/html/storage/logs/crash-game-error.log
chown -R unit:unit /var/www/html/storage/logs

echo "✅ Application setup completed"

# Start Unit in background
echo "🔧 Starting NGINX Unit..."
unitd --no-daemon &
UNIT_PID=$!

# Wait for Unit to be ready
sleep 3

# Start Supervisor
echo "📦 Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
