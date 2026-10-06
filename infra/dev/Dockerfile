# Development recipe only. No image was built during repository preparation.
FROM php:8.4-apache-bookworm

ENV APACHE_DOCUMENT_ROOT=/var/www/html/web

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
      git unzip libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev \
      libfreetype6-dev libonig-dev; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j2 gd intl pdo_mysql opcache zip mbstring exif; \
    a2enmod rewrite headers; \
    sed -ri 's!/var/www/html!/var/www/html/web!g' /etc/apache2/sites-available/000-default.conf; \
    printf '%s\n' \
      '<Directory /var/www/html>' \
      '  AllowOverride None' \
      '  Require all denied' \
      '</Directory>' \
      '<Directory /var/www/html/web>' \
      '  Options -Indexes +FollowSymLinks' \
      '  AllowOverride All' \
      '  Require all granted' \
      '</Directory>' \
      'SetEnvIfNoCase Cookie ".+" NERUDS_HAS_COOKIE=1' \
      'Header onsuccess unset Cache-Control env=NERUDS_HAS_COOKIE' \
      'Header always set Cache-Control "private, no-store" env=NERUDS_HAS_COOKIE' \
      '<LocationMatch "^/(user|admin|session|jsonapi)(/|$)">' \
      '  Header onsuccess unset Cache-Control' \
      '  Header always set Cache-Control "private, no-store"' \
      '</LocationMatch>' \
      'Header always set X-Content-Type-Options "nosniff"' \
      > /etc/apache2/conf-available/neruds-dev.conf; \
    a2enconf neruds-dev; \
    printf '%s\n' \
      'expose_php=Off' \
      'display_errors=Off' \
      'log_errors=On' \
      'memory_limit=384M' \
      'upload_max_filesize=32M' \
      'post_max_size=36M' \
      'session.cookie_httponly=1' \
      'session.cookie_samesite=Lax' \
      'session.cookie_secure=0' \
      'sendmail_path=/bin/false' \
      > /usr/local/etc/php/conf.d/neruds-dev.ini; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html
COPY . /var/www/html

# A future explicit build installs exactly composer.lock. No update is performed.
# Drupal's allowed installer/scaffold plugins remain necessary; project scripts
# are disabled. Review the imported composer allow-plugins before building.
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
      --no-interaction --prefer-dist --no-dev --no-progress --no-scripts \
    && mkdir -p web/sites/default/files /var/www/private /var/www/html/config/sync \
    && chown -R www-data:www-data web/sites/default/files /var/www/private

# Reapply the reviewed guard after Composer scaffold/plugins, then make it read-only.
COPY web/sites/default/settings.php /var/www/html/web/sites/default/settings.php
RUN chmod 0444 /var/www/html/web/sites/default/settings.php

EXPOSE 80
CMD ["apache2-foreground"]
