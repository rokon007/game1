FROM unit:1.34.1-php8.3

# Install only essential dependencies
RUN apt update && apt install -y \
    curl unzip git libicu-dev libzip-dev libpng-dev libjpeg-dev \
    libfreetype6-dev libssl-dev supervisor redis-tools procps \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pcntl opcache pdo pdo_mysql \
       intl zip gd exif bcmath sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# PHP configuration - OPTIMIZED for 4GB RAM
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.jit=tracing'; \
    echo 'opcache.jit_buffer_size=128M'; \
    echo 'memory_limit=256M'; \
    echo 'upload_max_filesize=32M'; \
    echo 'post_max_size=32M'; \
    echo 'max_execution_time=120'; \
    echo 'max_input_time=120'; \
    echo 'pcntl.async_signals=1'; \
} > /usr/local/etc/php/conf.d/custom.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

RUN mkdir -p storage/logs bootstrap/cache /var/log/supervisor

# Copy application files
COPY --chown=unit:unit . .

# Install dependencies and cleanup
RUN composer install --no-dev --optimize-autoloader --no-interaction --classmap-authoritative \
    && composer clear-cache \
    && rm -rf /root/.composer

# Copy configurations
COPY unit.json /docker-entrypoint.d/unit.json
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Set permissions
RUN chown -R unit:unit /var/www/html \
    && chmod -R 775 storage bootstrap/cache

HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:8000/ || exit 1

EXPOSE 8000

CMD ["/usr/local/bin/start.sh"]
