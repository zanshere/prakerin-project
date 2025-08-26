FROM php:8.2-cli

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependencies
COPY . .

RUN composer install

CMD php artisan serve --host=0.0.0.0 --port=8000ww
