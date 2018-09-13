#!/bin/sh
docker run -d -p 80:80 -v `pwd`:/var/www/html -v `pwd`/docker/db:/var/lib/mysql framework $*