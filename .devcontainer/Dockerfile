ARG PHP_IMAGE=8.5-fpm-bookworm
FROM php:${PHP_IMAGE}

LABEL maintainer="Roots Team"

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install system dependencies
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get -y install --no-install-recommends \
      ghostscript \
      jq \
      mariadb-client \
      vim \
      wget \
    && apt-get -y autoremove && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install PHP extensions
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
      bcmath \
      exif \
      gd \
      imagick \
      mysqli \
      opcache \
      pdo \
      pdo_mysql \
      redis \
      zip \
    && if [ -z "$CI" ]; then install-php-extensions xdebug; fi

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

ENV APPLICATION_PATH=/roots/app

USER vscode
