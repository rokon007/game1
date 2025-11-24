#!/bin/bash
set -e

echo "=========================================="
echo "🚀 Starting Crash Game Application"
echo "=========================================="

# Wait for database
echo "⏳ Waiting for database connection..."
attempt=0
max_attempts=30

until php artisan db:show >/dev/null 2>&1 || [ $attempt -eq $max_attempts ]; do
    attempt=$((attempt + 1))
    echo "Database not ready... Attempt $attempt/$max_attempts"
    sleep 2
done

if [ $attempt -eq $max_attempts ]; then
    echo "⚠️  Database timeout - proceeding anyway"
else
    echo "✅ Database connected"
fi

# Run migrations
echo "🔄 Running migrations..."
php artisan migrate --force 2>/dev/null || echo "⚠️  Migration skipped"

# Optimize application
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link 2>/dev/null || true

# Set permissions
echo "🔐 Setting permissions..."
mkdir -p /var/www/html/storage/logs
chown -R unit:unit /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create log files
touch /var/www/html/storage/logs/crash-game.log
touch /var/www/html/storage/logs/scheduler.log
chown unit:unit /var/www/html/storage/logs/*.log

echo "✅ Setup completed"
echo "=========================================="

# Start Unit in background
echo "🔧 Starting NGINX Unit..."
unitd &
UNIT_PID=$!
sleep 2

# Start Supervisor with both programs
echo "📦 Starting Supervisor..."
/usr/bin/supervisord -n -c /dev/stdin <<EOF
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisord.log
pidfile=/var/run/supervisord.pid
loglevel=info

[program:crash-game]
command=php /var/www/html/artisan crash:run
directory=/var/www/html
user=unit
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
startsecs=3
stopwaitsecs=30
stdout_logfile=/var/www/html/storage/logs/crash-game.log
stderr_logfile=/var/www/html/storage/logs/crash-game.log
redirect_stderr=true
priority=100

[program:scheduler]
command=/bin/sh -c "while true; do php /var/www/html/artisan schedule:run && sleep 60; done"
directory=/var/www/html
user=unit
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
startsecs=0
stdout_logfile=/var/www/html/storage/logs/scheduler.log
stderr_logfile=/var/www/html/storage/logs/scheduler.log
redirect_stderr=true
priority=90
EOF
