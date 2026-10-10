# ===================================================
# Stage 1: Build & Composer Dependencies Stage (Builder)
# ===================================================
FROM docker.io/dunglas/frankenphp:1.13.0-php8.4-alpine AS builder

# Install build dependencies & required extensions
RUN install-php-extensions \
    pdo_sqlite \
    zip \
    intl \
    opcache

# Copy Composer binary from official Composer image
COPY --from=docker.io/composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Cache dependencies layer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

# Copy application code and generate optimized autoloader
COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# ===================================================
# Stage 2: Minimal Production Runtime Stage (Runner)
# ===================================================
FROM docker.io/dunglas/frankenphp:1.13.0-php8.4-alpine AS runner

# Install only runtime extensions and curl for healthcheck
RUN apk add --no-cache curl \
    && install-php-extensions \
        pdo_sqlite \
        zip \
        intl \
        opcache

WORKDIR /app

# Copy application files and optimized vendor from builder stage
COPY --from=builder --chown=www-data:www-data /app /app

# Copy Caddyfile configuration
COPY --chown=www-data:www-data Caddyfile /etc/caddy/Caddyfile

# Copy entrypoint script and make it executable
COPY --chmod=755 docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# Ensure proper storage & database permissions for non-root execution
RUN mkdir -p /app/storage/framework/{sessions,views,cache} /app/storage/logs /app/bootstrap/cache /app/database /data /config \
    && touch /app/database/database.sqlite \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/database /data /config \
    && chmod -R 775 /app/storage /app/bootstrap/cache /app/database /data /config

# Enforce non-root execution (Tugas 5 rubric requirement)
USER www-data

# Container Healthcheck (Tugas 5 rubric requirement)
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:8000/up || exit 1

EXPOSE 8000

# Entrypoint runs migrations automatically, then starts FrankenPHP
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
