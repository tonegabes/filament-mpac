FROM php:8.3-apache-bullseye

RUN apt-get update && apt-get install -y \
  build-essential \
  curl \
  #git \
  jpegoptim optipng pngquant gifsicle \
  libavif-bin \
  libpng-dev \
  libxml2-dev \
  libzip-dev \
  locales \
  nano \
  #nodejs \
  #npm \
  unzip \
  vim \
  zip

RUN docker-php-ext-configure opcache --enable-opcache \
  && docker-php-ext-install \
  exif \
  bcmath \
  ctype \
  fileinfo \
  #json \
  mysqli \
  pdo_mysql \
  #tokenizer \
  xml \
  zip \
  intl \
  gd \
  && docker-php-ext-enable exif

COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

#COPY crontab /etc/crontabs/root

RUN apt-get update \
  && apt-get install libldap2-dev -y \
  && rm -rf /var/lib/apt/lists/* \
  && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
  && docker-php-ext-install ldap

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
  && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

#WORKDIR /var/www/html
COPY . /var/www/html
#COPY docker/start.sh /usr/local/bin/start

RUN composer install --prefer-dist --no-interaction --optimize-autoloader

RUN php artisan optimize:clear && php artisan storage:link \
  && php artisan icons:cache \
  && php artisan filament:cache-components \
  && chown -R www-data:www-data /var/www/html \
  && chmod -R ugo+rwx storage/ bootstrap/cache \
  #&& chmod u+x /usr/local/bin/start \
  && a2enmod rewrite

#CMD [ "/usr/local/bin/start" ]

#ENTRYPOINT ["bash", "init.sh" ]
