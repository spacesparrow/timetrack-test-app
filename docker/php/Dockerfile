    # ./docker/php/Dockerfile

#    FROM composer:2.0 as composer

    FROM php:7.4-fpm

    RUN docker-php-ext-install pdo_mysql

    RUN pecl install apcu

    RUN apt-get update && \
    apt-get install -y \
    libzip-dev libpng-dev

    RUN docker-php-ext-install zip
    RUN docker-php-ext-install gd
    RUN docker-php-ext-enable apcu

    WORKDIR /usr/src/app

    COPY --chown=1000:1000 ./ /usr/src/app

    COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

    RUN PATH=$PATH:/usr/src/app/vendor/bin:bin

    RUN composer install --no-scripts --prefer-dist \
      && rm -rf "$(composer config cache-dir)" "$(composer config data-dir)"