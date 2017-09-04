#!/bin/sh

if which composer
then
    composer install
else
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install
fi

