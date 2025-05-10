FROM php:7.4.5-apache

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN apt-get update && \
    apt-get install -y vim git libzip-dev zlib1g-dev zip unzip libxml2-dev libpng-dev supervisor ghostscript && \
    docker-php-ext-configure zip && \
    docker-php-ext-install pdo pdo_mysql zip opcache soap gd

COPY --from=composer:1.10.27 /usr/bin/composer /usr/bin/composer
RUN mkdir -p /var/log/supervisor
RUN rm -rf /etc/apache2/sites-available/* && \
    rm -rf /etc/apache2/sites-enabled/* && \
    rm -rf /var/www/* && \
    mkdir /var/www/app && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY php.ini /usr/local/etc/php/conf.d/eurotours-php.ini
COPY ./apache.conf /etc/apache2/sites-available/app.conf

RUN usermod -u 1000 www-data && \
    a2ensite app.conf && \
    a2enmod rewrite headers && \
    service apache2 restart

WORKDIR /var/www/app
COPY app /var/www/app
COPY run.sh /var/www/app/run.sh
COPY parameters.yml /var/www/app/app/config/parameters.yml
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --verbose --prefer-dist --no-suggest
RUN chmod +x /var/www/app/run.sh
RUN mkdir -p /var/www/app/var/ && \
    mkdir -p /var/www/app/var/cache && \
    mkdir -p /var/www/app/var/cache/prod && \
    chmod -R 777 /var/www/app/var/cache

EXPOSE 80

CMD [ "/var/www/app/run.sh" ]
