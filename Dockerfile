FROM docker.io/dunglas/frankenphp:1.13.0-php8.4

# Install required PHP extensions for Laravel and SQLite
RUN install-php-extensions \
    pdo_sqlite \
    zip \
    intl \
    opcache

# Copy official Composer binary
COPY --from=docker.io/composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory to FrankenPHP application root
WORKDIR /app

# Step 1: Copy Composer dependencies definition first for layer caching
COPY composer.json composer.lock ./

# Step 2: Install Composer dependencies before copying the application code
RUN composer install --no-interaction --no-scripts --no-autoloader --prefer-dist

# Step 3: Copy the rest of the application code
COPY . .

# Copy custom Caddyfile to FrankenPHP default location
COPY Caddyfile /etc/caddy/Caddyfile

# Step 4: Generate optimized autoloader and set up directory permissions
RUN composer dump-autoload --optimize \
    && mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database

# Expose standard FrankenPHP port
EXPOSE 8000

# Run FrankenPHP server
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
