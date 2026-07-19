# 1. PHP 8.3 Apache නිල Image එක ලබා ගැනීම
FROM php:8.3-apache

# 2. අවශ්‍ය කරන PHP extensions සහ GD library (webp/jpeg සඳහා) install කිරීම
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql gd zip \
    && a2enmod rewrite

# 3. 💡 Apache Document Root එක කෙලින්ම Laravel /public එකට හැරවීම (නියම ඩිවලොපර් ට්‍රික් එකක්!)
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# 4. Working directory එක සෙට් කිරීම
WORKDIR /var/www/html

# 5. අපේ කෝඩ් එක සර්වර් එකට කොපි කිරීම
COPY . .

# 6. Composer සෙට් කර dependencies install කිරීම (dev dependencies නැතුව)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# 7. Storage permissions සෙට් කිරීම
RUN chmod -R 777 storage bootstrap/cache

# 8. Port එක open කිරීම
EXPOSE 80