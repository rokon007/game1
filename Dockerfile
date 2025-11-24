FROM unit:1.34.1-php8.3

# Install dependencies
RUN apt update && apt install -y \
    curl unzip git libicu-dev libzip-dev libpng-dev libjpeg-dev \
    libfreetype6-dev libssl-dev supervisor redis-tools procps \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pcntl opcache pdo pdo_mysql \
       intl zip gd exif ftp bcmath sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# PHP configuration optimized for timing accuracy
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.jit=tracing'; \
    echo 'opcache.jit_buffer_size=256M'; \
    echo 'memory_limit=512M'; \
    echo 'upload_max_filesize=64M'; \
    echo 'post_max_size=64M'; \
    echo 'max_execution_time=300'; \
    echo 'max_input_time=300'; \
    echo 'pcntl.async_signals=1'; \
} > /usr/local/etc/php/conf.d/custom.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create necessary directories
RUN mkdir -p storage/logs bootstrap/cache \
    && mkdir -p /var/log/supervisor

# Copy application files
COPY --chown=unit:unit . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy unit configuration
COPY unit.json /docker-entrypoint.d/unit.json

# Copy startup script
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Set final permissions
RUN chown -R unit:unit /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:8000/ || exit 1

EXPOSE 8000

CMD ["/usr/local/bin/start.sh"]
