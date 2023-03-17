FROM php:8.2-fpm

RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

ADD ./dockerfiles/phpfpm/www.conf /usr/local/etc/php-fpm.d/www.conf