#!/bin/sh
if [ -d /Applications/XAMPP/bin ]
then # pick up the XAMPP on macOS
    PATH=/Applications/XAMPP/bin:$PATH
fi
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
    for i in . class/config assets class/pages twigs/content
    do
        chmod a+w $i
    done
else
    echo '******* Cannot find PHP!'
fi

