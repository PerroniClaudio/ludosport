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
    curl \
    nginx \
    supervisor \
    redis \
    meilisearch \
    prometheus \
    grafana \
    nodejs \
    npm

# Configura e installa le estensioni PHP necessarie
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd pcntl bcmath zip

# Installa l'estensione Redis
RUN pecl install redis && docker-php-ext-enable redis

# Installa Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Imposta la directory di lavoro
WORKDIR /var/www/html



# Copia i file dell'applicazione
COPY . .

# Esegui npm build
RUN npm install -g pnpm
RUN pnpm i
RUN pnpm build

# Installa le dipendenze del progetto
# Nota: questo passo potrebbe fallire se non hai un composer.json valido nella directory del progetto
RUN composer install --no-dev --optimize-autoloader || true

# Se il comando precedente fallisce, puoi decommentare la seguente riga per ignorare l'errore
# RUN echo "Composer install failed, but continuing anyway"

# Genera la chiave dell'applicazione (solo se non esiste già)
RUN php artisan key:generate --force

# Ottimizza la configurazione per la produzione
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Copia la configurazione di supervisord
COPY docker/nginx/nginx.prod.conf /etc/nginx/conf.d/default.conf
COPY ./ssl /etc/nginx/ssl
COPY ./docker/prometheus /etc/prometheus
COPY docker/supervisord2.conf /etc/supervisor/conf.d/supervisord.conf

# Imposta i permessi corretti
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Expose ports
EXPOSE 80 9000 6379 7700 9090 3000

# Avvia supervisord
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]