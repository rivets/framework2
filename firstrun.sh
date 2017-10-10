#!/bin/sh
if [ ! -d vendor/twig ]
then # looks like composer has not been run
    composer install
fi
for i in . .htaccess class/config assets
do
    chmod a+w $i
done
