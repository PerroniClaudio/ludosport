# Usa PHP 8.3 FPM Alpine come immagine base
FROM php:8.3-fpm-alpine

# Installa dipendenze di sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    redis \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip

# Installa estensioni PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo pdo_mysql pcntl bcmath gd zip 

# Installa Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Imposta la directory di lavoro
WORKDIR /var/www/html

# Copia i file dell'applicazione Laravel
COPY . .

# Installa le dipendenze di Laravel
RUN composer install --no-interaction --no-dev --prefer-dist

# Copia i file di configurazione
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Imposta i permessi
RUN chown -R www-data:www-data storage bootstrap/cache

# Espone le porte
EXPOSE 80 9000 6379

# Avvia i servizi usando Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]