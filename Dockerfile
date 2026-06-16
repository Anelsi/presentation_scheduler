FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

COPY docker/apache.conf /etc/apache2/conf-available/presentation-scheduler.conf

RUN a2enconf presentation-scheduler

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html

RUN if [ ! -d vendor ]; then composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader; fi \
    && chown -R www-data:www-data /var/www/html \
    && chmod +x /var/www/html/docker/entrypoint.sh

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]