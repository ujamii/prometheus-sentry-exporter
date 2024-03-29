FROM php:8.1-apache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN apt-get update && apt-get install -y git \
 && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && composer --version

COPY --chown=www-data:www-data . /var/www/html
RUN php /usr/local/bin/composer install --optimize-autoloader --no-dev  --prefer-dist \
 && apt-get clean \
 && rm -rf /tmp/* /var/lib/apt/lists/*
WORKDIR /var/www/html