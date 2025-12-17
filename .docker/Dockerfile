###############################################################################
# Image: PHP 8.3 + Apache for eQual framework
#
# Validation environment:
# - Host OS        : Ubuntu 24.04.1 LTS
# - Docker Engine  : 27.5.1
# - eQual version  : dev-2.0
# - Build date     : 2025-12-17
#
# Notes:
# - This image is intended for production-like environments
# - Apache + mod_php is used intentionally (no php-fpm)
###############################################################################
FROM php:8.3-apache
LABEL   Description="Debian with Apache and PHP + opcache with eQual dependencies" \
		Maintainer="Cedric Francoys <cedricfrancoys@gmail.com>" \
		License="Apache License 2.0" \
		Version="1.0"
RUN apt-get update && apt-get install -y \
	gnupg2 \
	git \
	procps \
	netcat-traditional \
	vim \
	wget
COPY ./mpm_prefork.conf /etc/apache2/mods-available/
COPY ./000-default.conf /etc/apache2/sites-available/
# install sqlsrv drivers
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
    | gpg --dearmor \
    | tee /usr/share/keyrings/microsoft.gpg > /dev/null
RUN echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft.gpg] \
https://packages.microsoft.com/debian/12/prod bookworm main" \
> /etc/apt/sources.list.d/microsoft-prod.list
RUN apt-get update

RUN ACCEPT_EULA=Y apt-get install -y --no-install-recommends \
    msodbcsql17 \
    unixodbc \
    unixodbc-dev
RUN pecl install sqlsrv pdo_sqlsrv
# install required PHP extensions
RUN set -ex; \
		\
		savedAptMark="$(apt-mark showmanual)"; \
		\
		apt-get update; \
		apt-get install -y --no-install-recommends \
				libfreetype6-dev \
				libjpeg62-turbo-dev \
				libxml2-dev \
				libjpeg-dev \
				libpng-dev \
				libwebp-dev \
				libzip-dev \
				libicu-dev \
				libonig-dev \
				zip \
				unzip \
		; \
		\
		docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
		docker-php-ext-configure intl; \
		docker-php-ext-install -j$(nproc) gd; \
		docker-php-ext-install opcache dom mbstring zip dom intl ftp; \
		docker-php-ext-install mysqli; \
		docker-php-ext-enable sqlsrv pdo_sqlsrv mbstring; \
		\
		apt-mark auto '.*' > /dev/null; \
		apt-mark manual $savedAptMark; \
		ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
				| awk '/=>/ { print $3 }' \
				| sort -u \
				| xargs -r dpkg-query -S \
				| cut -d: -f1 \
				| sort -u \
				| xargs -rt apt-mark manual; \
		\
		rm -rf /var/cache/apt/archives /var/lib/apt/lists/*
# set recommended PHP.ini settings
RUN { \
		echo 'opcache.memory_consumption=256'; \
		echo 'opcache.interned_strings_buffer=64'; \
		echo 'opcache.max_accelerated_files=8000'; \
		echo 'opcache.revalidate_freq=60'; \
		echo 'opcache.enable_cli=1'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

RUN a2enmod rewrite expires

VOLUME /var/www/html

CMD ["apache2-foreground"]