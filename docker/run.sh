#!/bin/bash
/etc/init.d/mysql start
/usr/sbin/apache2ctl -DFOREGROUND -k start
