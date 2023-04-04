FROM php:7.4-apache
LABEL   Description="Latest version of eQual with Apache UID and GUID injection" \
        Maintainer="Cedric Francoys <cedric@equal.run>" \
        License="Apache License 2.0" \
        Version="1.0"
ENV ACCEPT_EULA=Y
RUN apt-get update && apt-get install -y \
	gnupg2 \
    git \
    procps \
    netcat \
    vim
COPY ./mpm_prefork.conf /etc/apache2/mods-available/
COPY ./000-default.conf /etc/apache2/sites-available/
# install sqlsrv using pecl
#memo - latest version requires PHP 8
# RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
# RUN curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list > /etc/apt/sources.list.d/mssql-release.list
# RUN apt-get update
# RUN ACCEPT_EULA=Y apt-get -y --no-install-recommends install msodbcsql17 unixodbc-dev
# RUN pecl install sqlsrv pdo_sqlsrv
# install required PHP extensions
RUN set -ex; \
        \
        savedAptMark="$(apt-mark showmanual)"; \
        \
        apt-get update; \
        apt-get install -y --no-install-recommends \
                libfreetype6-dev \
                libjpeg-dev \
                libpng-dev \
                libwebp-dev \
                libtidy-dev \
                libzip-dev \
				libicu-dev \
                zip \
        ; \
        \
        docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
        docker-php-ext-install gd mysqli opcache zip tidy; \
#		docker-php-ext-enable sqlsrv pdo_sqlsrv; \
        \
# reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
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
        apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
        rm -rf /var/lib/apt/lists/*; \
        git clone https://github.com/cedricfrancoys/equal.git /var/www/html; \
        chgrp -R www-data log; \
        chgrp -R www-data bin; \
        chgrp -R www-data cache; \
        chgrp -R www-data config/routing; \
        chmod -R g+w log; \
        chmod -R g+w bin; \
        chmod -R g+w cache; \
        chmod -R g+w config/routing

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
                echo 'opcache.memory_consumption=256'; \
                echo 'opcache.interned_strings_buffer=64'; \
                echo 'opcache.max_accelerated_files=8000'; \
                echo 'opcache.revalidate_freq=60'; \
                echo 'opcache.fast_shutdown=1'; \
                echo 'opcache.enable_cli=1'; \
        } > /usr/local/etc/php/conf.d/opcache-recommended.ini
RUN a2enmod rewrite expires
VOLUME /var/www/html
CMD ["apache2-foreground"]