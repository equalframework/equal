#!/bin/bash
# install apache2
sudo apt-get update
sudo apt-get install apache2 libapache2-mod-fastcgi 
# enable php-fpm
sudo cp .travis/www.conf ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
sudo a2enmod rewrite actions alias proxy_fcgi
sudo sed -i -e "s,www-data,travis,g" /etc/apache2/envvars
sudo chown -R travis:travis /var/lib/apache2/fastcgi
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm
# configure apache virtual hosts
sudo cp -f .travis/travis-ci-apache /etc/apache2/sites-available/000-default.conf
cp -f .travis/travis-ci-apache7.3 /etc/apache2/sites-available/000-default.conf
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/000-default.conf
sudo service apache2 restart