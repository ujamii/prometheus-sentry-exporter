FROM php:7.2-apache
COPY --chown=www-data:www-data . /var/www/html
WORKDIR /var/www/html