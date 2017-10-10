#!/bin/sh
if which php >/dev/null
then # we do have PHP
    if [ ! -d vendor/twig ]
    then # looks like composer has not been run
        if which composer >/dev/null
        then # we have composer
            composer install
        else
            curl -sS https://getcomposer.org/installer | php
            php composer.phar install
        fi
    fi
    for i in . .htaccess class/config assets
    do
        chmod a+w $i
    done
else
    echo '******* Cannot find PHP!'
fi

