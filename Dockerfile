FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        cron \
        default-mysql-client \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg-dev \
        libonig-dev \
        libpng-dev \
        libssh2-1 \
        libssh2-1-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" mysqli pdo_mysql mbstring zip gd curl soap intl bcmath sockets \
    && (pecl install ssh2-1.3.1 && docker-php-ext-enable ssh2 || true) \
    && (pecl install redis && docker-php-ext-enable redis || true) \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

ENV APACHE_DOCUMENT_ROOT=/var/www/faoxima
WORKDIR /var/www/faoxima

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf \
    && printf '\n<Directory /var/www/faoxima>\n    AllowOverride All\n    Require all granted\n</Directory>\nServerName 0.0.0.0\n' \
        > /etc/apache2/conf-available/faoxima.conf \
    && a2enconf faoxima

COPY . /var/www/faoxima
COPY docker/php/conf.d/faoxima.ini /usr/local/etc/php/conf.d/faoxima.ini
COPY docker/faoxima.cron /etc/cron.d/faoxima
COPY docker/railway-entrypoint.sh /usr/local/bin/railway-entrypoint.sh

RUN chmod 0644 /etc/cron.d/faoxima \
    && chmod +x /usr/local/bin/railway-entrypoint.sh \
    && mkdir -p /var/www/faoxima/logs /var/www/faoxima/storage/cache /var/www/faoxima/storage/private /var/www/faoxima/cronbot/.runtime \
    && chown -R www-data:www-data /var/www/faoxima

ENTRYPOINT ["/usr/local/bin/railway-entrypoint.sh"]
CMD ["apache2-foreground"]
