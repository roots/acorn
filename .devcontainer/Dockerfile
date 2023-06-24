ARG PHP_IMAGE=8-fpm-bookworm
FROM php:${PHP_IMAGE}

LABEL maintainer="Roots Team"

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install dependencies
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get -y install --no-install-recommends \
      jq \
      mariadb-client \
      vim \
      zip \
    && apt-get -y autoremove && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install PHP extensions
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get -y install --no-install-recommends \
      ghostscript \
      libfreetype6-dev \
      libjpeg-dev \
      libmagickwand-dev \
      libpng-dev \
      libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j "$(nproc)" \
      bcmath \
      exif \
      gd \
      mysqli \
      opcache \
      pdo \
      pdo_mysql \
      zip \
    && pecl install imagick-3.7.0 && docker-php-ext-enable imagick \
    && pecl install -o -f redis && docker-php-ext-enable redis \
    && pecl install xdebug-3.2.1 && docker-php-ext-enable xdebug \
    && apt-get -y autoremove && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer clear-cache \
    && echo "export PATH=${PATH}:./vendor/bin:/roots/app/vendor/bin" >> ~/.bashrc

# Install wp-cli
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp \
    && mkdir -p /etc/bash_completion.d \
    && curl -O https://raw.githubusercontent.com/wp-cli/wp-cli/master/utils/wp-completion.bash \
    && mv wp-completion.bash /etc/bash_completion.d/wp-completion.bash

USER vscode

