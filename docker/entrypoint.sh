#!/bin/sh
set -e

cd /var/www/html

echo "Running database seed..."
php database/seed.php || echo "Seed failed or already completed, continuing..."

echo "Starting Apache..."
exec apache2-foreground