#!/bin/bash
if [ ! -e /var/lib/mysql/framework ]
then
    /usr/sbin/mysqld --initialize-insecure --init-file=/tmp/init.sql
    cd /var/www/html
    if [ ! -e class ]
    then
        git clone https://github.com/rivets/framework2.git .
    fi
    ./firstrun.sh
    cd /
fi
/etc/init.d/mysql start
/usr/sbin/apache2ctl -DFOREGROUND -k start
