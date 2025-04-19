# Laravel 10 এবং PHP 8.1 এর জন্য Dockerfile

 # অফিসিয়াল Unit+PHP ইমেজ ব্যবহার করুন
FROM unit:1.31.1-php8.1 

# কাজের ডিরেক্টরি সেট করা
WORKDIR /var/www

# প্রয়োজনীয় সিস্টেম প্যাকেজ ইনস্টল করা
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    bcmath \
    ctype \
    fileinfo \
    mbstring \
    tokenizer \
    xml \
    zip \
    gd

# Composer ইনস্টল করা
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Laravel নির্ভরশীলতা ইনস্টল করা
COPY . /var/www
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Laravel ক্যাশ ক্লিয়ার করা
RUN php artisan config:clear && php artisan cache:clear

# পোর্ট 9000 এক্সপোজ করা
EXPOSE 9000

# কনটেইনার চালু হলে PHP-FPM চালানো
# CMD ["php-fpm"]
