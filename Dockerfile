FROM dunglas/frankenphp:1-php8.3-alpine

WORKDIR /app

# Install system dependencies & common PHP extensions
RUN apk add --no-cache \
    git \
    unzip \
    sqlite \
    sqlite-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev

RUN install-php-extensions \
    pdo_mysql \
    pdo_sqlite \
    sqlite3 \
    gd \
    zip \
    bcmath \
    intl \
    opcache \
    pcntl

# PHP production configuration
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/docker-php-ram.ini \
    && echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/docker-php-ram.ini \
    && echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/docker-php-ram.ini \
    && echo "variables_order = 'EGPCS'" >> /usr/local/etc/php/conf.d/docker-php-ram.ini

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy source code
COPY . /app

# Ensure environment exists for build-time package discovery
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Run Composer install
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Prepare directories and permissions
RUN mkdir -p /app/storage/framework/cache/data \
             /app/storage/framework/sessions \
             /app/storage/framework/views \
             /app/storage/framework/testing \
             /app/storage/logs \
             /app/storage/app/public \
             /app/database \
    && touch /app/database/database.sqlite \
    && chmod -R 777 /app/storage /app/bootstrap/cache /app/database

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Railway assigns port via $PORT env var
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
