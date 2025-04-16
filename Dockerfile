# Step 1: Base image with PHP & extensions
FROM php:8.2-fpm

# Step 2: System dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Step 3: Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Step 4: Set working directory
WORKDIR /var/www/html

# Step 5: Copy app files
COPY . .

# Step 6: Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Step 7: Laravel dependencies install
RUN composer install --optimize-autoloader --no-dev

# Step 8: Expose port (optional if using nginx separately)
EXPOSE 9000

# Step 9: Final command
CMD ["php-fpm"]

