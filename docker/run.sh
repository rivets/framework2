#!/bin/bash
if [ -e /var/lib/mysql/empty ]
then
   rm /var/lib/mysql/empty
   /usr/sbin/mysqld --initialize-insecure --init-file=/tmp/init.sql
fi
/etc/init.d/mysql start
/usr/sbin/apache2ctl -DFOREGROUND -k start
