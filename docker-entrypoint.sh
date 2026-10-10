#!/bin/sh
set -e

# Ensure database directory and SQLite file exist on mounted volume
mkdir -p /app/database
touch /app/database/database.sqlite

# Run database migrations automatically
php artisan migrate --force

# Execute the main container command (FrankenPHP)
exec "$@"
