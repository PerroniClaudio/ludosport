# Usa l'immagine ufficiale di PHP 8.3 con FPM
FROM php:8.3-fpm

# Installa le dipendenze necessarie
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

# Configura e installa le estensioni PHP necessarie
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd pcntl bcmath zip

# Installa Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Imposta la directory di lavoro
WORKDIR /var/www/html

# Copia i file dell'applicazione
COPY . .

# Installa le dipendenze del progetto
RUN composer install --no-dev --optimize-autoloader

# Genera la chiave dell'applicazione
RUN php artisan key:generate

# Ottimizza la configurazione per la produzione
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Imposta i permessi corretti
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Esponi la porta 9000 per FPM
EXPOSE 9000

# Avvia PHP-FPM
CMD ["php-fpm"]