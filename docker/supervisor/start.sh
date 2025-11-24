#!/bin/bash
set -e

echo "🚀 Starting Application (4GB RAM Optimized)"

# Wait for database (shorter timeout)
attempt=0
max_attempts=20

until php artisan db:show >/dev/null 2>&1 || [ $attempt -eq $max_attempts ]; do
    attempt=$((attempt + 1))
    echo "Waiting for DB... $attempt/$max_attempts"
    sleep 1
done

[ $attempt -eq $max_attempts ] && echo "⚠️  DB timeout" || echo "✅ DB connected"

# Run migrations
php artisan migrate --force 2>/dev/null || true

# Optimize (less aggressive caching)
php artisan config:cache
php artisan route:cache

# Set permissions
mkdir -p /var/www/html/storage/logs
chown -R unit:unit /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

touch /var/www/html/storage/logs/crash-game.log
touch /var/www/html/storage/logs/scheduler.log

echo "✅ Setup completed"

# Start Unit
unitd &
sleep 2

# Start Supervisor with minimal resources
/usr/bin/supervisord -n -c /dev/stdin <<EOF
[supervisord]
nodaemon=true
user=root
logfile=/dev/null
pidfile=/var/run/supervisord.pid
loglevel=warn
minfds=1024
minprocs=50

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
